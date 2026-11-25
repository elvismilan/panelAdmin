<?php
/**
 * Contenido del listado del Blog (sitio publico).
 * Incluido por app/Views/shared/layout.php (moduleView = 'blog/index').
 *
 * Variables: $posts, $pagination, $categoryFilters, $activeCategorySlug,
 * $activeCategoryName.
 */
use PanelAdmin\Core\Helpers\Img;
use PanelAdmin\Core\Url;

$posts = is_array($posts ?? null) ? $posts : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$categoryFilters = is_array($categoryFilters ?? null) ? $categoryFilters : [];
$activeCategorySlug = trim((string) ($activeCategorySlug ?? ''));
$activeCategoryName = trim((string) ($activeCategoryName ?? ''));

$currentPage = (int) ($pagination['currentPage'] ?? 1);
$totalPages = (int) ($pagination['totalPages'] ?? 1);
$buildPageUrl = static function (int $page) use ($pagination): string {
    $query = is_array($pagination['query'] ?? null) ? $pagination['query'] : [];
    if ($page > 1) {
        $query['page'] = (string) $page;
    } else {
        unset($query['page']);
    }

    $url = Url::to('/blog');
    return $query === [] ? $url : $url . '?' . http_build_query($query);
};

/** @return array<int,int|string> */
$buildPageItems = static function (int $current, int $total): array {
    if ($total <= 7) {
        return range(1, $total);
    }

    $items = [1];
    $start = max(2, $current - 1);
    $end = min($total - 1, $current + 1);

    if ($start > 2) {
        $items[] = '…';
    }
    for ($page = $start; $page <= $end; $page++) {
        $items[] = $page;
    }
    if ($end < $total - 1) {
        $items[] = '…';
    }
    $items[] = $total;

    return $items;
};
?>
<section class="page-hero">
    <div class="container">
        <h1>Blog</h1>
        <?php if ($activeCategoryName !== ''): ?>
            <p class="page-hero-lead">Categoría: <?= htmlspecialchars($activeCategoryName, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <nav class="breadcrumbs" aria-label="Migas de pan">
            <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>">Inicio</a>
            <span>/</span>
            <span>Blog</span>
        </nav>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($categoryFilters !== []): ?>
            <nav class="category-filter" aria-label="Filtrar por categoría">
                <a href="<?= htmlspecialchars(Url::to('/blog'), ENT_QUOTES, 'UTF-8') ?>"
                   class="category-filter-pill<?= $activeCategorySlug === '' ? ' is-active' : '' ?>">Todas</a>
                <?php foreach ($categoryFilters as $filter): ?>
                    <a href="<?= htmlspecialchars((string) $filter['href'], ENT_QUOTES, 'UTF-8') ?>"
                       class="category-filter-pill<?= (bool) ($filter['is_current'] ?? false) ? ' is-active' : '' ?>">
                        <?= htmlspecialchars((string) $filter['name'], ENT_QUOTES, 'UTF-8') ?>
                        <span class="category-filter-count"><?= (int) ($filter['count'] ?? 0) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($posts === []): ?>
            <p class="blog-empty">Todavía no hay artículos publicados<?= $activeCategoryName !== '' ? ' en esta categoría' : '' ?>.</p>
        <?php else: ?>
            <div class="blog-grid blog-grid--listing">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card blog-card--with-image">
                        <?php if ((string) ($post['image_card'] ?? '') !== ''): ?>
                            <a href="<?= htmlspecialchars((string) $post['href'], ENT_QUOTES, 'UTF-8') ?>" class="blog-card-media">
                                <?= Img::tag((string) $post['image_card'], (string) $post['title']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if ((string) ($post['created_label'] ?? '') !== ''): ?>
                            <span class="blog-date"><?= htmlspecialchars((string) $post['created_label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <h3><a href="<?= htmlspecialchars((string) $post['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                        <?php if ((string) ($post['excerpt'] ?? '') !== ''): ?>
                            <p><?= htmlspecialchars((string) $post['excerpt'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <a href="<?= htmlspecialchars((string) $post['href'], ENT_QUOTES, 'UTF-8') ?>" class="read-more">Leer más →</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Paginación del blog">
                    <?php if ((bool) ($pagination['hasPrev'] ?? false)): ?>
                        <a href="<?= htmlspecialchars($buildPageUrl((int) $pagination['prevPage']), ENT_QUOTES, 'UTF-8') ?>" class="pagination-arrow" aria-label="Página anterior">←</a>
                    <?php else: ?>
                        <span class="pagination-arrow is-disabled" aria-hidden="true">←</span>
                    <?php endif; ?>

                    <ul class="pagination-pages">
                        <?php foreach ($buildPageItems($currentPage, $totalPages) as $item): ?>
                            <li>
                                <?php if ($item === '…'): ?>
                                    <span class="pagination-ellipsis">…</span>
                                <?php elseif ((int) $item === $currentPage): ?>
                                    <span class="pagination-page is-active" aria-current="page"><?= (int) $item ?></span>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($buildPageUrl((int) $item), ENT_QUOTES, 'UTF-8') ?>" class="pagination-page"><?= (int) $item ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ((bool) ($pagination['hasNext'] ?? false)): ?>
                        <a href="<?= htmlspecialchars($buildPageUrl((int) $pagination['nextPage']), ENT_QUOTES, 'UTF-8') ?>" class="pagination-arrow" aria-label="Página siguiente">→</a>
                    <?php else: ?>
                        <span class="pagination-arrow is-disabled" aria-hidden="true">→</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
