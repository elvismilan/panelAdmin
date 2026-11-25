<?php
/**
 * Contenido del detalle de un articulo del Blog (sitio publico).
 * Incluido por app/Views/shared/layout.php (moduleView = 'blog/detalle').
 *
 * Variables: $post, $previousPost, $nextPost, $recentPosts.
 */
use PanelAdmin\Core\Helpers\SocialIconHelper;
use PanelAdmin\Core\Helpers\Img;
use PanelAdmin\Core\Url;

$post = is_array($post ?? null) ? $post : [];
$previousPost = is_array($previousPost ?? null) ? $previousPost : null;
$nextPost = is_array($nextPost ?? null) ? $nextPost : null;
$recentPosts = is_array($recentPosts ?? null) ? $recentPosts : [];
$postTitle = (string) ($post['title'] ?? 'Artículo');
$categoryLinks = is_array($post['category_links'] ?? null) ? $post['category_links'] : [];
$postUrl = (string) ($post['href'] ?? Url::to('/blog'));

$fbIcon = SocialIconHelper::resolve('facebook');
$liIcon = SocialIconHelper::resolve('linkedin');
$waIcon = SocialIconHelper::resolve('whatsapp');

$shareLinks = [
    [
        'label' => 'Compartir en Facebook',
        'class' => 'share-btn--facebook',
        'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($postUrl),
        'icon' => $fbIcon,
    ],
    [
        'label' => 'Compartir en LinkedIn',
        'class' => 'share-btn--linkedin',
        'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($postUrl),
        'icon' => $liIcon,
    ],
    [
        'label' => 'Compartir en WhatsApp',
        'class' => 'share-btn--whatsapp',
        'url' => 'https://api.whatsapp.com/send?text=' . rawurlencode($postTitle . ' ' . $postUrl),
        'icon' => $waIcon,
    ],
];
$emailShareUrl = 'mailto:?subject=' . rawurlencode($postTitle) . '&body=' . rawurlencode($postTitle . "\n" . $postUrl);
?>
<section class="page-hero">
    <div class="container container--narrow">
        <h1><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if ($categoryLinks !== []): ?>
            <div class="blog-detail-categories">
                <?php foreach ($categoryLinks as $category): ?>
                    <a href="<?= htmlspecialchars((string) $category['href'], ENT_QUOTES, 'UTF-8') ?>" class="category-filter-pill category-filter-pill--on-dark">
                        <?= htmlspecialchars((string) $category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <nav class="breadcrumbs" aria-label="Migas de pan">
            <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>">Inicio</a>
            <span>/</span>
            <a href="<?= htmlspecialchars(Url::to('/blog'), ENT_QUOTES, 'UTF-8') ?>">Blog</a>
            <span>/</span>
            <span><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
    </div>
</section>

<article class="section blog-detail">
    <div class="container">
        <div class="blog-detail-layout">
            <div class="blog-detail-main">
                <?php if ((string) ($post['image_detail'] ?? '') !== ''): ?>
                    <div class="blog-detail-media">
                        <?= Img::tag((string) $post['image_detail'], $postTitle, [], 'priority') ?>
                    </div>
                <?php endif; ?>

                <div class="blog-detail-meta">
                    <?php if ((string) ($post['created_label'] ?? '') !== ''): ?>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                            <?= htmlspecialchars((string) $post['created_label'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                    <?php if ((string) ($post['author'] ?? '') !== ''): ?>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Por <?= htmlspecialchars((string) $post['author'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        <?= (int) ($post['read_minutes'] ?? 1) ?> min de lectura
                    </span>
                </div>

                <div class="blog-detail-content">
                    <?= $post['content_html'] ?? '' ?>
                </div>

            </div>

            <aside class="blog-sidebar">
                <?php if ($recentPosts !== []): ?>
                    <div class="sidebar-widget">
                        <h3 class="sidebar-widget-title">Posts recientes</h3>
                        <ul class="sidebar-recent-posts">
                            <?php foreach ($recentPosts as $recent): ?>
                                <li>
                                    <a href="<?= htmlspecialchars((string) $recent['href'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ((string) ($recent['image_thumb'] ?? '') !== ''): ?>
                                            <?= Img::tag((string) $recent['image_thumb'], '', ['class' => 'sidebar-recent-thumb']) ?>
                                        <?php endif; ?>
                                        <span class="sidebar-recent-text">
                                            <span class="sidebar-recent-title"><?= htmlspecialchars((string) $recent['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ((string) ($recent['created_label'] ?? '') !== ''): ?>
                                                <span class="sidebar-recent-date"><?= htmlspecialchars((string) $recent['created_label'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">Compartir</h3>
                    <div class="share-row">
                        <?php foreach ($shareLinks as $share): ?>
                            <a href="<?= htmlspecialchars((string) $share['url'], ENT_QUOTES, 'UTF-8') ?>"
                               class="share-btn <?= htmlspecialchars((string) $share['class'], ENT_QUOTES, 'UTF-8') ?>"
                               target="_blank" rel="noopener noreferrer"
                               aria-label="<?= htmlspecialchars((string) $share['label'], ENT_QUOTES, 'UTF-8') ?>">
                                <svg viewBox="<?= htmlspecialchars((string) $share['icon']['viewBox'], ENT_QUOTES, 'UTF-8') ?>" fill="currentColor" aria-hidden="true">
                                    <path d="<?= htmlspecialchars((string) $share['icon']['d'], ENT_QUOTES, 'UTF-8') ?>"/>
                                </svg>
                            </a>
                        <?php endforeach; ?>
                        <a href="<?= htmlspecialchars($emailShareUrl, ENT_QUOTES, 'UTF-8') ?>" class="share-btn share-btn--email" aria-label="Compartir por correo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</article>

<?php if ($previousPost !== null || $nextPost !== null): ?>
    <section class="section section--muted" aria-label="Navegación entre artículos">
        <div class="container">
            <div class="post-nav">
                <?php if ($previousPost !== null): ?>
                    <a href="<?= htmlspecialchars((string) $previousPost['href'], ENT_QUOTES, 'UTF-8') ?>" class="post-nav-item post-nav-item--prev">
                        <?php if ((string) ($previousPost['image_thumb'] ?? '') !== ''): ?>
                            <?= Img::tag((string) $previousPost['image_thumb'], '', ['class' => 'post-nav-thumb']) ?>
                        <?php endif; ?>
                        <span class="post-nav-text">
                            <span class="post-nav-title"><?= htmlspecialchars((string) $previousPost['title'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="post-nav-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                Anterior
                            </span>
                        </span>
                    </a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <?php if ($nextPost !== null): ?>
                    <a href="<?= htmlspecialchars((string) $nextPost['href'], ENT_QUOTES, 'UTF-8') ?>" class="post-nav-item post-nav-item--next">
                        <span class="post-nav-text">
                            <span class="post-nav-title"><?= htmlspecialchars((string) $nextPost['title'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="post-nav-label">
                                Siguiente
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                        <?php if ((string) ($nextPost['image_thumb'] ?? '') !== ''): ?>
                            <?= Img::tag((string) $nextPost['image_thumb'], '', ['class' => 'post-nav-thumb']) ?>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
