<?php

namespace Core\Filter;

/**
 * Builds reusable filter configurations for index views.
 *
 * Usage in a controller:
 *
 *   $filterBar = FilterBar::make()
 *       ->chips('padre', 'Padre', $model->getPadreFilterOptions())
 *       ->chips('estado', 'Estado', [
 *           ['value' => 'H', 'label' => 'Habilitado'],
 *           ['value' => 'I', 'label' => 'Inhabilitado'],
 *       ]);
 *
 *   $activeFilters = $filterBar->active($queryParams);
 *   // Pass $filterBar->toView($queryParams, '/ruta') to the view as $filterBarGroups
 */
class FilterBar
{
    private array $groups = [];

    private function __construct() {}

    public static function make(): self
    {
        return new self();
    }

    /**
     * Add a chips filter group.
     *
     * @param string $param      GET parameter name (e.g. 'padre', 'estado')
     * @param string $label      Display label shown before the chips
     * @param array  $options    Each item: ['value' => '', 'label' => '', 'count' => null]
     * @param string $allLabel   Label for the "show all" chip
     */
    public function chips(string $param, string $label, array $options, string $allLabel = 'Todos'): self
    {
        $clone = clone $this;
        $clone->groups = $this->groups;
        $clone->groups[] = [
            'type'     => 'chips',
            'param'    => $param,
            'label'    => $label,
            'allLabel' => $allLabel,
            'options'  => $options,
        ];
        return $clone;
    }

    /**
     * Returns active filter values keyed by param name.
     * Always returns a key for every registered group (empty string = no filter).
     */
    public function active(array $queryParams): array
    {
        $result = [];
        foreach ($this->groups as $group) {
            $result[$group['param']] = trim((string) ($queryParams[$group['param']] ?? ''));
        }
        return $result;
    }

    /**
     * Returns only active filters that have a non-empty value.
     * Useful for passing to model query methods.
     */
    public function activeFilters(array $queryParams): array
    {
        return array_filter($this->active($queryParams), fn($v) => $v !== '');
    }

    /**
     * Returns view-ready config for the filter-bar component.
     *
     * @param array  $queryParams  Current GET params (from getQueryParams or request->getParams)
     * @param string $basePath     Route path, e.g. '/modulos' (without SITE_ROOT, the component adds it via $siteRoot)
     */
    public function toView(array $queryParams, string $basePath): array
    {
        $groups = [];

        foreach ($this->groups as $group) {
            $current = trim((string) ($queryParams[$group['param']] ?? ''));

            $chips = [];

            // "All" chip — clears this filter
            $chips[] = $this->buildChip(
                '',
                $group['allLabel'],
                null,
                $current === '',
                $queryParams,
                $group['param'],
                '',
                $basePath
            );

            foreach ($group['options'] as $opt) {
                $value = (string) ($opt['value'] ?? '');
                $chips[] = $this->buildChip(
                    $value,
                    (string) ($opt['label'] ?? $value),
                    isset($opt['count']) ? (int) $opt['count'] : null,
                    $current === $value,
                    $queryParams,
                    $group['param'],
                    $value,
                    $basePath
                );
            }

            $groups[] = [
                'label'   => $group['label'],
                'param'   => $group['param'],
                'current' => $current,
                'chips'   => $chips,
            ];
        }

        return $groups;
    }

    private function buildChip(
        string $value,
        string $label,
        ?int   $count,
        bool   $active,
        array  $queryParams,
        string $param,
        string $newValue,
        string $basePath
    ): array {
        $params = $queryParams;
        unset($params['page']); // always reset to page 1 on filter change

        if ($newValue === '') {
            unset($params[$param]);
        } else {
            $params[$param] = $newValue;
        }

        // Drop empty values so the URL stays clean
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null);

        $qs  = empty($params) ? '' : '?' . http_build_query($params);
        $url = $basePath . $qs;

        return [
            'value'  => $value,
            'label'  => $label,
            'count'  => $count,
            'active' => $active,
            'url'    => $url,
        ];
    }
}
