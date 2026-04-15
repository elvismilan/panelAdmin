<?php

namespace Core\Helpers;

class MenuHelper
{
    public static function renderAdminMenu(array $items, string $currentPath): string
    {
        $html = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $html[] = self::renderItem($item, $currentPath, 0);
        }

        return implode("\n", array_filter($html));
    }

    private static function renderItem(array $item, string $currentPath, int $level): string
    {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            return '';
        }

        $url = trim((string) ($item['url'] ?? '#'));
        $children = is_array($item['children'] ?? null) ? $item['children'] : [];
        $hasChildren = !empty($children);
        $active = self::isActive($item, $currentPath);

        if ($level === 0) {
            $iconClass = IconHelper::normalizeForMenu((string) ($item['icon'] ?? ''), 'fa fa-circle-o');
            if ($hasChildren) {
                $childrenHtml = self::renderChildren($children, $currentPath, 1);

                return '<li class="dropdown">'
                    . '<a class="nav-link menu-title' . ($active ? ' active' : '') . '" href="javascript:void(0)">'
                    . '<i class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>'
                    . '<span>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>'
                    . '</a>'
                    . '<ul class="nav-submenu menu-content">' . $childrenHtml . '</ul>'
                    . '</li>';
            }

            $href = $url !== '' ? $url : 'javascript:void(0)';

            return '<li class="dropdown">'
                . '<a class="nav-link menu-title link-nav' . ($active ? ' active' : '') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
                . '<i class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i>'
                . '<span>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>'
                . '</a>'
                . '</li>';
        }

        if ($hasChildren) {
            $childrenHtml = self::renderChildren($children, $currentPath, $level + 1);

            return '<li>'
                . '<a class="submenu-title' . ($active ? ' active' : '') . '" href="javascript:void(0)">'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
                . '<span class="sub-arrow"><i class="fa fa-chevron-right"></i></span>'
                . '</a>'
                . '<ul class="nav-sub-childmenu submenu-content">' . $childrenHtml . '</ul>'
                . '</li>';
        }

        $href = $url !== '' ? $url : '#';

        return '<li>'
            . '<a class="' . ($active ? 'active' : '') . '" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
            . '</a>'
            . '</li>';
    }

    private static function renderChildren(array $children, string $currentPath, int $level): string
    {
        $html = [];
        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }
            $rendered = self::renderItem($child, $currentPath, $level);
            if ($rendered !== '') {
                $html[] = $rendered;
            }
        }

        return implode('', $html);
    }

    private static function isActive(array $item, string $currentPath): bool
    {
        $url = trim((string) ($item['url'] ?? ''));
        if ($url !== '' && $url !== '#' && self::isPathActive($url, $currentPath)) {
            return true;
        }

        $children = is_array($item['children'] ?? null) ? $item['children'] : [];
        foreach ($children as $child) {
            if (is_array($child) && self::isActive($child, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    private static function isPathActive(string $menuPath, string $currentPath): bool
    {
        $menuBase = rtrim(trim($menuPath), '/');
        $currentBase = rtrim(trim($currentPath), '/');

        if ($menuBase === '') {
            $menuBase = '/';
        }
        if ($currentBase === '') {
            $currentBase = '/';
        }

        return $menuBase === $currentBase || str_starts_with($currentBase, $menuBase . '/');
    }
}
