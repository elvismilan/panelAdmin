<?php
$paginationData = is_array($pagination ?? null) ? $pagination : null;
$paginationAriaLabel = (string) ($paginationAriaLabel ?? 'Paginacion');
$paginationClass = (string) ($paginationClass ?? 'pagination justify-content-center pagination-primary pagination-sm');
//$showPaginationSummary = (bool) ($showPaginationSummary ?? true);
?>
<?php if ($paginationData !== null): ?>
    <?php
    $basePath = (string) ($paginationData['basePath'] ?? '');
    $currentPage = (int) ($paginationData['currentPage'] ?? 1);
    $totalPages = max((int) ($paginationData['totalPages'] ?? 1), 1);
    $query = is_array($paginationData['query'] ?? null) ? $paginationData['query'] : [];
    $makePageUrl = static function (int $page) use ($basePath, $query): string {
        $params = $query;
        $params['page'] = $page;
        $qs = http_build_query($params);

        return $basePath . ($qs !== '' ? '?' . $qs : '');
    };
    ?>

    <nav class="m-t-20" aria-label="<?= htmlspecialchars($paginationAriaLabel, ENT_QUOTES, 'UTF-8') ?>">
        <ul class="<?= htmlspecialchars($paginationClass, ENT_QUOTES, 'UTF-8') ?>">
            <?php if ((bool) ($paginationData['hasPrev'] ?? false)): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($makePageUrl((int) ($paginationData['prevPage'] ?? 1)), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Anterior</span></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item<?= $i === $currentPage ? ' active' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($makePageUrl($i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ((bool) ($paginationData['hasNext'] ?? false)): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= htmlspecialchars($makePageUrl((int) ($paginationData['nextPage'] ?? 1)), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
