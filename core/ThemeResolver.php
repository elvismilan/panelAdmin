<?php

namespace Core;

class ThemeResolver
{
    private string $viewsPath;
    private string $themesPath;

    public function __construct(?string $viewsPath = null, ?string $themesPath = null)
    {
        $this->viewsPath = $viewsPath ?: dirname(__DIR__) . '/app/Views';
        $this->themesPath = $themesPath ?: dirname(__DIR__) . '/resources/themes';
    }

    public function resolve(string $area, string $defaultView): string
    {
        $area = strtolower(trim($area));
        $defaultPack = $this->defaultPackForArea($area);
        $pack = $this->resolvePackForArea($area, $defaultPack);
        $option = trim((string) ($_ENV[strtoupper($area) . '_THEME_OPTION'] ?? '1'));
        if (in_array($area, ['login', 'admin'], true)) {
            $option = '1';
        }
        $mappedOptionView = $this->shouldUseOptionMap($area, $defaultView)
            ? $this->optionViewForArea($area, $option)
            : null;

        $themeCandidates = [];

        $defaultView = ltrim($defaultView, '/');
        if ($defaultView !== '') {
            $themeCandidates[] = "{$pack}/{$area}/{$defaultView}";
        }

        if ($mappedOptionView !== null) {
            $themeCandidates[] = "{$pack}/{$area}/{$mappedOptionView}";
        }

        $themeCandidates[] = "{$pack}/{$area}/option{$option}";
        $themeCandidates[] = "{$pack}/{$area}/index";

        foreach ($themeCandidates as $view) {
            if ($this->themeViewExists($view)) {
                return 'resources:themes/' . $view;
            }
        }

        if ($this->appViewExists($defaultView)) {
            return $defaultView;
        }

        throw new \RuntimeException("No available view for area '{$area}'.");
    }

    private function appViewExists(string $view): bool
    {
        return is_file($this->viewsPath . '/' . $view . '.php');
    }

    private function themeViewExists(string $view): bool
    {
        return is_file($this->themesPath . '/' . $view . '.php');
    }

    private function defaultPackForArea(string $area): string
    {
        if ($area === 'public') {
            return 'public';
        }

        return 'default';
    }

    private function resolvePackForArea(string $area, string $defaultPack): string
    {
        $requestedPack = strtolower(trim((string) ($_ENV[strtoupper($area) . '_THEME_PACK'] ?? $defaultPack)));

        if ($requestedPack === '') {
            return $defaultPack;
        }

        if ($area !== 'public' && $requestedPack === 'public') {
            return $defaultPack;
        }

        return $requestedPack;
    }

    private function optionViewForArea(string $area, string $option): ?string
    {
        $optionMap = [
            'login' => [
                '1' => 'login-form-default',
            ],
            'admin' => [
                '1' => 'dashboard-default',
            ],
        ];

        return $optionMap[$area][$option] ?? null;
    }

    private function shouldUseOptionMap(string $area, string $defaultView): bool
    {
        $normalized = ltrim(strtolower($defaultView), '/');

        return ($area === 'login' && in_array($normalized, ['login/index', 'auth/login'], true))
            || ($area === 'admin' && $normalized === 'dashboard/index');
    }
}