<?php

namespace Core\Helpers;

use App\Models\NotificacionModel;
use Core\Auth;
use Throwable;

class NotificationHelper
{
    private const LIMIT = 5;

    private const TIPO = [
        'success' => ['li' => 'noti-success',   'bg' => 'bg-light-success',   'icon' => 'check-circle'],
        'warning' => ['li' => 'noti-secondary', 'bg' => 'bg-light-secondary', 'icon' => 'edit'],
        'danger'  => ['li' => 'noti-danger',    'bg' => 'bg-light-danger',    'icon' => 'trash-2'],
        'info'    => ['li' => 'noti-primary',   'bg' => 'bg-light-primary',   'icon' => 'activity'],
    ];

    /**
     * Renderiza el <li> completo del dropdown de notificaciones para el header admin.
     */
    public static function renderDropdown(): string
    {
        [$noLeidas, $items] = self::fetch();

        $html  = self::renderTrigger($noLeidas);
        $html .= '<ul class="notification-dropdown onhover-show-div">';
        $html .= self::renderHeader($noLeidas);
        $html .= $items !== [] ? self::renderItems($items) : self::renderEmpty();
        $html .= '<li class="text-center"><a class="f-w-700" href="' . htmlspecialchars(\Core\Url::to('/notificaciones'), ENT_QUOTES, 'UTF-8') . '">Ver todas</a></li>';
        $html .= '</ul>';

        return '<li class="onhover-dropdown">' . $html . '</li>';
    }

    // -------------------------------------------------------------------------

    /** @return array{int, array<int, array<string,mixed>>} */
    private static function fetch(): array
    {
        try {
            $user = Auth::user();
            $usuId = trim((string) ($user['id'] ?? ''));
            if ($usuId === '') {
                return [0, []];
            }

            $model    = new NotificacionModel();
            $noLeidas = $model->countNoLeidas($usuId);
            $items    = $model->paginate(0, self::LIMIT, '', '', '0', $usuId);
            return [$noLeidas, $items];
        } catch (Throwable) {
            return [0, []];
        }
    }

    private static function renderTrigger(int $noLeidas): string
    {
        $dot = $noLeidas > 0 ? '<span class="dot-animated"></span>' : '';
        return '<div class="notification-box"><i data-feather="bell"></i>' . $dot . '</div>';
    }

    private static function renderHeader(int $noLeidas): string
    {
        if ($noLeidas === 0) {
            return '<li><p class="f-w-700 mb-0">Sin notificaciones nuevas</p></li>';
        }

        $plural  = $noLeidas !== 1;
        $texto   = $noLeidas . ' notificacion' . ($plural ? 'es' : '') . ' nueva' . ($plural ? 's' : '');
        $badge   = '<span class="pull-right badge badge-primary badge-pill">' . $noLeidas . '</span>';

        return '<li><p class="f-w-700 mb-0">' . $texto . $badge . '</p></li>';
    }

    /** @param array<int, array<string,mixed>> $items */
    private static function renderItems(array $items): string
    {
        $html = '';
        foreach ($items as $noti) {
            $tipo  = (string) ($noti['noti_tipo'] ?? 'info');
            $meta  = self::TIPO[$tipo] ?? self::TIPO['info'];
            $id    = urlencode((string) ($noti['noti_id']    ?? ''));
            $titulo = htmlspecialchars((string) ($noti['noti_titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
            $fecha  = htmlspecialchars((string) ($noti['noti_fecha']  ?? ''), ENT_QUOTES, 'UTF-8');

            $html .= '<li class="' . $meta['li'] . '">'
                . '<a href="' . htmlspecialchars(\Core\Url::to('/notificaciones/' . $id . '/ver'), ENT_QUOTES, 'UTF-8') . '" style="text-decoration:none;color:inherit;">'
                . '<div class="media">'
                . '<span class="notification-bg ' . $meta['bg'] . '">'
                . '<i data-feather="' . $meta['icon'] . '"></i>'
                . '</span>'
                . '<div class="media-body"><p>' . $titulo . '</p><span>' . $fecha . '</span></div>'
                . '</div>'
                . '</a>'
                . '</li>';
        }

        return $html;
    }

    private static function renderEmpty(): string
    {
        return '<li><div class="media"><div class="media-body text-center py-2">'
            . '<p class="text-muted mb-0">No hay notificaciones pendientes.</p>'
            . '</div></div></li>';
    }
}
