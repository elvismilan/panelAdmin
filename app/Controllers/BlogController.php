<?php

namespace App\Controllers;

use App\Models\BlogModel;
use PanelAdmin\Core\Controller;
use PanelAdmin\Core\Helpers\ImageHelper;
use PanelAdmin\Core\Url;
use Throwable;

class BlogController extends Controller
{
    private const PER_PAGE = 6;

    public function index(): void
    {
        $categorySlug = $this->getQueryParam('categoria', '');
        $selectedCategory = null;
        $categoryId = null;
        $items = [];
        $categoriesRows = [];

        $page = $this->getCurrentPage();
        $pagination = $this->buildPagination(0, $page, self::PER_PAGE, '/blog', [
            'categoria' => $categorySlug,
        ]);

        try {
            $model = new BlogModel();
            if ($categorySlug !== '') {
                $selectedCategory = $model->findCategoryBySlug($categorySlug);
                if (!is_array($selectedCategory)) {
                    $this->renderNotFound('No encontramos esta categoría', 'La categoría solicitada no existe o ya no está disponible.');
                    return;
                }
                $categoryId = (int) ($selectedCategory['cat_id'] ?? 0);
                $categorySlug = trim((string) ($selectedCategory['cat_slug'] ?? $categorySlug));
            }

            $totalRows = $model->countPublished($categoryId);
            $pagination = $this->buildPagination($totalRows, $page, self::PER_PAGE, '/blog', [
                'categoria' => $categorySlug,
            ]);
            $items = $model->paginatePublished((int) $pagination['offset'], (int) $pagination['perPage'], $categoryId);
            $categoriesRows = $model->getActiveCategories();
        } catch (Throwable $e) {
            error_log('BlogController index error: ' . $e->getMessage());
        }

        $posts = array_map(fn(array $row): array => $this->normalizePost($row), $items);
        $categoryFilters = $this->buildCategoryFilters($categoriesRows, $categorySlug);
        $categoryName = trim((string) ($selectedCategory['cat_name'] ?? ''));
        $siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
        $title = ($categoryName !== '' ? ('Blog: ' . $categoryName) : 'Blog') . ' | ' . $siteTitle;
        $metaDescription = 'Artículos y novedades de ' . $siteTitle . '.';

        $canonicalQuery = [];
        if ($categorySlug !== '') {
            $canonicalQuery['categoria'] = $categorySlug;
        }
        if ($page > 1) {
            $canonicalQuery['page'] = (string) $page;
        }
        $canonical = Url::to('/blog') . ($canonicalQuery !== [] ? '?' . http_build_query($canonicalQuery) : '');

        $firstImage = '';
        foreach ($posts as $post) {
            if ((string) ($post['image_card'] ?? '') !== '') {
                $firstImage = (string) $post['image_card'];
                break;
            }
        }

        $this->renderPublicModule('blog/index', [
            'title' => $title,
            'metaDescription' => $metaDescription,
            'canonical' => $canonical,
            'ogType' => 'website',
            'ogImage' => $firstImage,
            'jsonLd' => [$this->buildBlogCollectionSchema($posts, $canonical, $title)],
            'posts' => $posts,
            'pagination' => $pagination,
            'categoryFilters' => $categoryFilters,
            'activeCategorySlug' => $categorySlug,
            'activeCategoryName' => $categoryName,
            'activeNav' => 'blog',
            'pageAssets' => $this->pageAssets(),
        ]);
    }

    public function detalle(string $slug): void
    {
        $slug = trim($slug);
        if ($slug === '') {
            $this->redirect('/blog');
            return;
        }

        try {
            $model = new BlogModel();
            $row = $model->findPublishedBySlug($slug);
            if (!is_array($row)) {
                $this->renderNotFound('No encontramos este artículo', 'El artículo solicitado no existe o ya no está disponible.');
                return;
            }

            $post = $this->normalizePost($row);
            $canonicalSlug = trim((string) ($post['slug'] ?? ''));
            if ($canonicalSlug !== '' && $canonicalSlug !== $slug) {
                $this->redirect('/blog/' . rawurlencode($canonicalSlug), 301);
                return;
            }

            $model->incrementViews((string) ($row['id'] ?? '0'));
            $recentRows = $model->getLatestPublished(3, (string) ($row['id'] ?? ''));
            $recentPosts = array_map(fn(array $item): array => $this->normalizePost($item), $recentRows);

            $createdAt = (string) ($row['created'] ?? '');
            $postId = (string) ($row['id'] ?? '');
            $previousRow = $model->findPreviousPublished($createdAt, $postId);
            $nextRow = $model->findNextPublished($createdAt, $postId);
            $previousPost = $previousRow !== null ? $this->normalizePost($previousRow) : null;
            $nextPost = $nextRow !== null ? $this->normalizePost($nextRow) : null;
        } catch (Throwable $e) {
            error_log('BlogController detalle error: ' . $e->getMessage());
            $this->redirect('/blog');
            return;
        }

        $siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
        $postTitle = $post['title'] !== '' ? $post['title'] : 'Artículo';
        $canonical = (string) ($post['href'] ?? Url::to('/blog/' . rawurlencode($slug)));
        $ogImage = (string) ($post['image_detail'] ?? '');

        $this->renderPublicModule('blog/detalle', [
            'title' => $postTitle . ' | ' . $siteTitle,
            'metaDescription' => (string) ($post['excerpt'] ?? ''),
            'canonical' => $canonical,
            'ogType' => 'article',
            'ogImage' => $ogImage,
            'jsonLd' => [$this->buildBlogPostingSchema($post, $canonical, $siteTitle)],
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'recentPosts' => $recentPosts,
            'activeNav' => 'blog',
            'pageAssets' => $this->pageAssets(),
        ]);
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
            ],
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $categoriesRows
     * @return array<int,array<string,mixed>>
     */
    private function buildCategoryFilters(array $categoriesRows, string $activeCategorySlug): array
    {
        $filters = [];
        foreach ($categoriesRows as $categoryRow) {
            $slugValue = trim((string) ($categoryRow['cat_slug'] ?? ''));
            $nameValue = trim((string) ($categoryRow['cat_name'] ?? ''));
            if ($slugValue === '' || $nameValue === '') {
                continue;
            }

            $filters[] = [
                'name' => $nameValue,
                'slug' => $slugValue,
                'href' => Url::to('/blog?categoria=' . rawurlencode($slugValue)),
                'count' => max(0, (int) ($categoryRow['total_posts'] ?? 0)),
                'is_current' => $activeCategorySlug !== '' && strcasecmp($activeCategorySlug, $slugValue) === 0,
            ];
        }

        return $filters;
    }

    /** @return array<string,mixed> */
    private function normalizePost(array $row): array
    {
        $title = trim((string) ($row['titulo'] ?? ''));
        $slug = trim((string) ($row['slug'] ?? ''));
        $excerpt = trim((string) ($row['detalle'] ?? ''));
        $content = trim((string) ($row['contenido'] ?? ''));
        $author = trim((string) ($row['autor'] ?? ''));
        $createdAt = trim((string) ($row['created'] ?? ''));
        $modifiedAt = trim((string) ($row['modified'] ?? ''));
        $imageRaw = trim((string) ($row['imagen'] ?? ''));
        $categoryPairsRaw = trim((string) ($row['category_pairs_raw'] ?? ''));

        if ($excerpt === '' && $content !== '') {
            $plain = trim(strip_tags($content));
            if ($plain !== '') {
                $excerpt = mb_strlen($plain) > 180 ? rtrim(mb_substr($plain, 0, 180)) . '…' : $plain;
            }
        }

        $categoryLinks = [];
        if ($categoryPairsRaw !== '') {
            foreach (explode('||', $categoryPairsRaw) as $pairRaw) {
                $pairRaw = trim($pairRaw);
                if ($pairRaw === '') {
                    continue;
                }
                $parts = explode('::', $pairRaw, 2);
                $name = trim((string) ($parts[0] ?? ''));
                $slugPart = trim((string) ($parts[1] ?? ''));
                if ($name === '' || $slugPart === '') {
                    continue;
                }
                $categoryLinks[] = [
                    'name' => $name,
                    'slug' => $slugPart,
                    'href' => Url::to('/blog?categoria=' . rawurlencode($slugPart)),
                ];
            }
        }

        return [
            'id' => (string) ($row['id'] ?? ''),
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content_html' => $content,
            'author' => $author,
            'created' => $createdAt,
            'modified' => $modifiedAt,
            'created_label' => $this->formatDateEs($createdAt),
            'read_minutes' => $this->estimateReadMinutes($content !== '' ? $content : $excerpt),
            'category_links' => $categoryLinks,
            'image_card' => $this->resolveImage($imageRaw, '641x430'),
            'image_thumb' => $this->resolveImage($imageRaw, '150x150'),
            'image_detail' => $this->resolveImage($imageRaw, '1600x900'),
            'href' => $slug !== '' ? Url::to('/blog/' . rawurlencode($slug)) : Url::to('/blog'),
        ];
    }

    private function resolveImage(string $storedValue, string $size): string
    {
        if ($storedValue === '') {
            return '';
        }

        $path = ImageHelper::thumb($storedValue, 'blogs', $size);
        if ($path === '') {
            return '';
        }

        return Url::to('/' . ltrim($path, '/'));
    }

    private function formatDateEs(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }

        $ts = strtotime($date);
        if ($ts === false) {
            return '';
        }

        $months = [
            '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
            '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
            '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre',
        ];

        return date('d', $ts) . ' de ' . ($months[date('m', $ts)] ?? date('m', $ts)) . ' de ' . date('Y', $ts);
    }

    private function estimateReadMinutes(string $text): int
    {
        $plain = trim(strip_tags($text));
        if ($plain === '') {
            return 1;
        }

        $words = str_word_count($plain);
        return $words > 0 ? max(1, (int) ceil($words / 180)) : 1;
    }

    private function toIsoDate(string $date): string
    {
        $date = trim($date);
        if ($date === '') {
            return '';
        }

        $ts = strtotime($date);
        return $ts === false ? '' : date('c', $ts);
    }

    private function resolveSiteLogoUrl(): string
    {
        return Url::to('/assets/public-theme/img/logo-blue.svg');
    }

    /** @param array<string,mixed> $post */
    private function buildBlogPostingSchema(array $post, string $canonical, string $siteTitle): array
    {
        $publishedIso = $this->toIsoDate((string) ($post['created'] ?? ''));
        $modifiedIso = $this->toIsoDate((string) ($post['modified'] ?? '')) ?: $publishedIso;
        $headline = trim((string) ($post['title'] ?? ''));
        $description = trim((string) ($post['excerpt'] ?? ''));
        $image = trim((string) ($post['image_detail'] ?? ''));
        $author = trim((string) ($post['author'] ?? ''));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $headline !== '' ? $headline : 'Artículo',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
            'url' => $canonical,
            'inLanguage' => 'es',
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteTitle,
                'logo' => ['@type' => 'ImageObject', 'url' => $this->resolveSiteLogoUrl()],
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $author !== '' ? $author : $siteTitle,
            ],
        ];

        if ($description !== '') {
            $schema['description'] = $description;
        }
        if ($image !== '') {
            $schema['image'] = [$image];
        }
        if ($publishedIso !== '') {
            $schema['datePublished'] = $publishedIso;
        }
        if ($modifiedIso !== '') {
            $schema['dateModified'] = $modifiedIso;
        }

        return $schema;
    }

    /** @param array<int,array<string,mixed>> $posts */
    private function buildBlogCollectionSchema(array $posts, string $canonical, string $title): array
    {
        $itemList = [];
        $position = 1;
        foreach ($posts as $post) {
            $name = trim((string) ($post['title'] ?? ''));
            $url = trim((string) ($post['href'] ?? ''));
            if ($name === '' || $url === '') {
                continue;
            }

            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $name,
                'url' => $url,
            ];
            $position++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $title,
            'url' => $canonical,
            'inLanguage' => 'es',
            'isPartOf' => Url::to('/'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $itemList,
            ],
        ];
    }

    /** 404 real (no redirect) para que Google no lo trate como soft-404. */
    private function renderNotFound(string $heading, string $message): void
    {
        http_response_code(404);
        $this->view->render('resources:themes/admin/errors/not-found', [
            'title' => 'Página no encontrada',
            'code' => 404,
            'heading' => $heading,
            'message' => $message,
            'homeUrl' => Url::to('/'),
        ]);
    }
}
