<?php

namespace App\Controllers;

use App\Models\ContactoModel;
use App\Models\ContenidosModel;
use PanelAdmin\Core\Controller;
use PanelAdmin\Core\EmailMessages;
use App\Messages\FlashMessages;
use PanelAdmin\Core\Mailer;
use PanelAdmin\Core\NotificacionService;
use PanelAdmin\Core\Recaptcha;
use PanelAdmin\Core\Url;
use PanelAdmin\Core\Validator;
use App\Messages\ValidationMessages;
use Throwable;

class ContactoController extends Controller
{
    private const PUBLIC_MAX_RECENT_PER_IP = 3;
    private const PUBLIC_RECENT_WINDOW_MINUTES = 30;

    public function index(): void
    {
        $siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
        $metaDescription = 'Ponte en contacto con ' . $siteTitle . '. Escríbenos y te responderemos lo antes posible.';

        try {
            $contactInfo = (new ContenidosModel())->findActiveById(4);
        } catch (Throwable) {
            $contactInfo = null;
        }

        $email = trim((string) ($contactInfo['link'] ?? ''));
        $phone = trim((string) ($_ENV['SITE_CONTACT_PHONE'] ?? ''));
        if ($email === '') {
            $email = trim((string) ($_ENV['SITE_CONTACT_EMAIL'] ?? ''));
        }

        $this->renderPublicModule('contacto/index', [
            'title' => 'Contacto | ' . $siteTitle,
            'metaDescription' => $metaDescription,
            'canonical' => Url::to('/contacto'),
            'jsonLd' => [[
                '@context' => 'https://schema.org',
                '@type' => 'ContactPage',
                'name' => 'Contacto',
                'url' => Url::to('/contacto'),
                'description' => $metaDescription,
                'inLanguage' => 'es',
            ]],
            'activeNav' => 'contacto',
            'contactEmail' => $email,
            'contactPhone' => $phone,
            'recaptchaEnabled' => Recaptcha::isEnabled(),
            'recaptchaSiteKey' => Recaptcha::siteKey(),
            'pageAssets' => $this->pageAssets(),
        ]);
    }

    public function enviar(): void
    {
        $this->requireCsrf();

        $params = $this->request->getParams();
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $model = new ContactoModel();

        try {
            if ($ip !== '' && $model->countRecentByIp($ip, self::PUBLIC_RECENT_WINDOW_MINUTES) >= self::PUBLIC_MAX_RECENT_PER_IP) {
                $this->jsonResponse(['ok' => false, 'error' => ValidationMessages::CONTACTO_RATE_LIMIT], 429);
                return;
            }
        } catch (Throwable) {
            // Si el chequeo falla no bloqueamos el envio.
        }

        [$passes, $error, $errors] = $this->validatePublicForm($params, $ip);
        if (!$passes) {
            $this->jsonResponse(['ok' => false, 'error' => $error, 'errors' => $errors], 422);
            return;
        }

        $params['ip'] = $ip;
        $params['user_agent'] = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

        try {
            $contactoId = $model->createFromPublic($params);
        } catch (Throwable $e) {
            error_log('ContactoController::enviar error: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => ValidationMessages::CONTACTO_SAVE_ERROR], 500);
            return;
        }

        $this->logAction($model->getLastActionLog(), 'CREATE');

        try {
            NotificacionService::registrar('contactos', 'CREATE', 'PUBLICO', $contactoId);
        } catch (Throwable $e) {
            error_log('ContactoController notificacion error: ' . $e->getMessage());
        }

        $this->sendNotificationEmail($params);

        $this->jsonResponse(['ok' => true, 'message' => FlashMessages::CONTACTO_PUBLIC_RECEIVED]);
    }

    /** @return array<string,mixed> */
    private function pageAssets(): array
    {
        return [
            'css' => [Url::to('/assets/public-theme/css/style.css')],
            'js' => [
                Url::to('/assets/public-theme/js/main.js'),
                Url::to('/assets/public-theme/js/mega-menu.js'),
                Url::to('/assets/public-theme/js/nav-scroll.js'),
                Url::to('/assets/public-theme/js/contacto-form.js'),
            ],
        ];
    }

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validatePublicForm(array $params, string $ip = ''): array
    {
        $validator = Validator::make($params, [
            'nombre' => 'required|string|min:2|max:100',
            'telefono' => 'required|string|min:6|max:30',
            'email' => 'nullable|string|max:180',
            'mensaje' => 'required|string|min:10',
        ], [
            'nombre' => 'Nombre',
            'telefono' => 'Telefono',
            'email' => 'Correo electronico',
            'mensaje' => 'Mensaje',
        ]);

        $passes = $validator->passes();
        $errors = $validator->errors();
        $error = $validator->first();

        $email = trim((string) ($params['email'] ?? ''));
        if ($passes && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $passes = false;
            $error = ValidationMessages::CONTACTO_EMAIL_INVALID;
            $errors['email'] = [$error];
        }

        if ($passes && Recaptcha::isEnabled()) {
            $token = trim((string) ($params['g-recaptcha-response'] ?? ''));
            if ($token === '') {
                $passes = false;
                $error = ValidationMessages::CONTACTO_RECAPTCHA_REQUIRED;
                $errors['recaptcha'] = [$error];
            } elseif (!Recaptcha::verify($token, $ip)) {
                $passes = false;
                $error = ValidationMessages::CONTACTO_RECAPTCHA_INVALID;
                $errors['recaptcha'] = [$error];
            }
        }

        return [$passes, $error, $errors];
    }

    private function sendNotificationEmail(array $params): void
    {
        $to = trim((string) ($_ENV['CONTACTO_NOTIFY_EMAIL'] ?? ''));
        if ($to === '') {
            $to = trim((string) ($_ENV['MAIL_FROM_ADDRESS'] ?? ''));
        }
        if ($to === '') {
            error_log('ContactoController: sin CONTACTO_NOTIFY_EMAIL ni fallback configurado; email omitido.');
            return;
        }

        try {
            $siteTitle = EmailMessages::siteTitle();
            $html = $this->emailTemplatesRenderer->render(EmailMessages::TEMPLATE_CONTACTO_NUEVO, [
                'siteTitle' => $siteTitle,
                'nombre' => (string) ($params['nombre'] ?? ''),
                'telefono' => (string) ($params['telefono'] ?? ''),
                'email' => (string) ($params['email'] ?? ''),
                'mensaje' => (string) ($params['mensaje'] ?? ''),
                'address' => (string) ($_ENV['ADDRESS'] ?? ''),
                'country' => (string) ($_ENV['COUNTRY'] ?? ''),
            ]);

            (new Mailer())->send($to, EmailMessages::contactoNuevoSubject($siteTitle), $html);
        } catch (Throwable $e) {
            // El email es informativo: si falla, el mensaje ya quedo registrado.
            error_log('ContactoController email error: ' . $e->getMessage());
        }
    }
}
