<?php

namespace App\Controllers;

use App\Models\RedesSocialesModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;

class RedesSocialesController extends Controller
{
    /** @var array<int, array{value:string,label:string}> */
    private const SOCIAL_ICON_OPTIONS = [
        ['value' => 'fa-brands fa-facebook', 'label' => 'Facebook'],
        ['value' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
        ['value' => 'fa-brands fa-x-twitter', 'label' => 'X (Twitter)'],
        ['value' => 'fa-brands fa-youtube', 'label' => 'YouTube'],
        ['value' => 'fa-brands fa-tiktok', 'label' => 'TikTok'],
        ['value' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
        ['value' => 'fa-brands fa-whatsapp', 'label' => 'WhatsApp'],
        ['value' => 'fa-brands fa-telegram', 'label' => 'Telegram'],
        ['value' => 'fa-brands fa-pinterest-p', 'label' => 'Pinterest'],
        ['value' => 'fa-brands fa-snapchat', 'label' => 'Snapchat'],
        ['value' => 'fa-brands fa-discord', 'label' => 'Discord'],
        ['value' => 'fa-brands fa-twitch', 'label' => 'Twitch'],
        ['value' => 'fa-brands fa-reddit-alien', 'label' => 'Reddit'],
        ['value' => 'fa-brands fa-github', 'label' => 'GitHub'],
        ['value' => 'fa-brands fa-gitlab', 'label' => 'GitLab'],
        ['value' => 'fa-brands fa-medium', 'label' => 'Medium'],
        ['value' => 'fa-brands fa-vimeo-v', 'label' => 'Vimeo'],
        ['value' => 'fa-brands fa-spotify', 'label' => 'Spotify'],
        ['value' => 'fa-brands fa-apple', 'label' => 'Apple'],
        ['value' => 'fa-brands fa-google', 'label' => 'Google'],
        ['value' => 'fa-brands fa-threads', 'label' => 'Threads'],
        ['value' => 'fa-brands fa-line', 'label' => 'Line'],
        ['value' => 'fa-brands fa-weixin', 'label' => 'WeChat'],
        ['value' => 'fa-brands fa-skype', 'label' => 'Skype'],
        ['value' => 'fa-brands fa-slack', 'label' => 'Slack'],
        ['value' => 'fa-brands fa-dribbble', 'label' => 'Dribbble'],
        ['value' => 'fa-brands fa-behance', 'label' => 'Behance'],
        ['value' => 'fa-brands fa-flickr', 'label' => 'Flickr'],
        ['value' => 'fa-brands fa-wordpress', 'label' => 'WordPress'],
        ['value' => 'fa-solid fa-link', 'label' => 'Sitio web / enlace'],
    ];

    public function index(): void
    {
        $this->requireAuth();

        $list = $this->searchableIndex('/redes', RedesSocialesModel::class, 'Buscar por titulo, link o icono...');

        $this->renderAdminModule('redes_sociales/index', [
            'title' => UiMessages::REDES_SOCIALES_INDEX_TITLE,
            'user' => Auth::user(),
            'items' => $list['items'],
            'pagination' => $list['pagination'],
            'search' => $list['search'],
            'searchConfig' => $list['searchConfig'],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderCreateForm([
            'res_titulo' => '',
            'res_link' => '',
            'res_icon' => '',
            'res_estado' => 'H',
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new RedesSocialesModel();
        $params = $this->request->getParams();

        if (trim((string) ($params['res_estado'] ?? '')) === '') {
            $params['res_estado'] = 'H';
        }

        $validator = Validator::make($params, [
            'res_titulo' => 'required|string|min:2|max:32',
            'res_link' => 'required|string|min:4|max:128',
            'res_icon' => 'required|string|min:2|max:32',
            'res_estado' => 'required|in:H,D',
        ], [
            'res_titulo' => 'Titulo',
            'res_link' => 'Link',
            'res_icon' => 'Icono',
            'res_estado' => 'Estado',
        ]);

        if (!$validator->passes()) {
            $this->renderCreateForm($params, $validator->first(), $validator->errors());
            return;
        }

        $params['res_icon'] = self::normalizeSocialIconClass((string) ($params['res_icon'] ?? ''));
        if (!self::isAllowedSocialIcon($params['res_icon'])) {
            $this->renderCreateForm($params, ValidationMessages::REDES_SOCIALES_ICON_INVALID);
            return;
        }

        $titulo = trim((string) ($params['res_titulo'] ?? ''));
        if ($model->existsByTitle($titulo)) {
            $this->renderCreateForm($params, ValidationMessages::REDES_SOCIALES_ALREADY_EXISTS);
            return;
        }

        $createdId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('redes_sociales', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $createdId);

        $this->flashSuccess(FlashMessages::REDES_SOCIALES_CREATED);
        $this->redirect('/redes');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $item = (new RedesSocialesModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/redes');
            return;
        }

        $this->renderEditForm($id, $item);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new RedesSocialesModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/redes');
            return;
        }

        $params = $this->request->getParams();
        $validator = Validator::make($params, [
            'res_titulo' => 'required|string|min:2|max:32',
            'res_link' => 'required|string|min:4|max:128',
            'res_icon' => 'required|string|min:2|max:32',
            'res_estado' => 'required|in:H,D',
        ], [
            'res_titulo' => 'Titulo',
            'res_link' => 'Link',
            'res_icon' => 'Icono',
            'res_estado' => 'Estado',
        ]);

        if (!$validator->passes()) {
            $this->renderEditForm($id, $params, $validator->first(), $validator->errors());
            return;
        }

        $params['res_icon'] = self::normalizeSocialIconClass((string) ($params['res_icon'] ?? ''));
        if (!self::isAllowedSocialIcon($params['res_icon'])) {
            $this->renderEditForm($id, $params, ValidationMessages::REDES_SOCIALES_ICON_INVALID);
            return;
        }

        $titulo = trim((string) ($params['res_titulo'] ?? ''));
        if ($model->existsByTitle($titulo, $id)) {
            $this->renderEditForm($id, $params, ValidationMessages::REDES_SOCIALES_ALREADY_EXISTS);
            return;
        }

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('redes_sociales', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::REDES_SOCIALES_UPDATED);
        $this->redirect('/redes');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new RedesSocialesModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/redes');
            return;
        }

        $this->renderAdminModule('redes_sociales/eliminar', [
            'title' => UiMessages::REDES_SOCIALES_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new RedesSocialesModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/redes');
            return;
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('redes_sociales', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::REDES_SOCIALES_DELETED);
        $this->redirect('/redes');
    }

    private static function isAllowedSocialIcon(string $iconClass): bool
    {
        $iconClass = self::normalizeSocialIconClass($iconClass);
        if ($iconClass === '') {
            return false;
        }

        foreach (self::SOCIAL_ICON_OPTIONS as $option) {
            if ($option['value'] === $iconClass) {
                return true;
            }
        }

        return false;
    }

    private function renderCreateForm(array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('redes_sociales/agregar', [
            'title' => UiMessages::REDES_SOCIALES_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'iconOptions' => self::socialIconOptionsWithSelected((string) ($form['res_icon'] ?? '')),
            'form' => $form,
        ]);
    }

    private function renderEditForm(string $id, array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('redes_sociales/editar', [
            'title' => UiMessages::REDES_SOCIALES_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'iconOptions' => self::socialIconOptionsWithSelected((string) ($form['res_icon'] ?? '')),
            'form' => $form,
            'itemId' => $id,
        ]);
    }

    private static function normalizeSocialIconClass(string $iconClass): string
    {
        $normalized = trim($iconClass);
        if ($normalized === '') {
            return '';
        }

        $legacyMap = [
            'fa fa-facebook' => 'fa-brands fa-facebook',
            'fa-brands fa-facebook-f' => 'fa-brands fa-facebook',
            'fa fa-instagram' => 'fa-brands fa-instagram',
            'fa fa-twitter' => 'fa-brands fa-x-twitter',
            'fa fa-youtube-play' => 'fa-brands fa-youtube',
            'fa fa-music' => 'fa-brands fa-tiktok',
            'fa fa-linkedin' => 'fa-brands fa-linkedin-in',
            'fa fa-whatsapp' => 'fa-brands fa-whatsapp',
            'fa fa-telegram' => 'fa-brands fa-telegram',
            'fa fa-pinterest-p' => 'fa-brands fa-pinterest-p',
            'fa fa-snapchat-ghost' => 'fa-brands fa-snapchat',
            'fa fa-comments' => 'fa-brands fa-discord',
            'fa fa-gamepad' => 'fa-brands fa-twitch',
            'fa fa-reddit-alien' => 'fa-brands fa-reddit-alien',
            'fa fa-github' => 'fa-brands fa-github',
            'fa fa-gitlab' => 'fa-brands fa-gitlab',
            'fa fa-medium' => 'fa-brands fa-medium',
            'fa fa-vimeo' => 'fa-brands fa-vimeo-v',
            'fa fa-spotify' => 'fa-brands fa-spotify',
            'fa fa-apple' => 'fa-brands fa-apple',
            'fa fa-google' => 'fa-brands fa-google',
            'fa fa-comments-o' => 'fa-brands fa-threads',
            'fa fa-commenting-o' => 'fa-brands fa-line',
            'fa fa-wechat' => 'fa-brands fa-weixin',
            'fa fa-skype' => 'fa-brands fa-skype',
            'fa fa-slack' => 'fa-brands fa-slack',
            'fa fa-dribbble' => 'fa-brands fa-dribbble',
            'fa fa-behance' => 'fa-brands fa-behance',
            'fa fa-flickr' => 'fa-brands fa-flickr',
            'fa fa-wordpress' => 'fa-brands fa-wordpress',
            'fa fa-globe' => 'fa-solid fa-link',
        ];

        return $legacyMap[$normalized] ?? $normalized;
    }

    /** @return array<int, array{value:string,label:string}> */
    private static function socialIconOptionsWithSelected(?string $selected): array
    {
        $options = self::SOCIAL_ICON_OPTIONS;
        $selectedValue = trim((string) $selected);
        if ($selectedValue === '') {
            return $options;
        }

        foreach ($options as $option) {
            if ($option['value'] === $selectedValue) {
                return $options;
            }
        }

        $options[] = [
            'value' => $selectedValue,
            'label' => 'Actual (legacy): ' . $selectedValue,
        ];

        return $options;
    }
}
