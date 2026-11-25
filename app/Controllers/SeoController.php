<?php

namespace App\Controllers;

use App\Models\BlogModel;
use App\Models\ServicioModel;
use App\Models\SliderModel;
use PanelAdmin\Core\Controller;
use PanelAdmin\Core\Url;
use Throwable;

class SeoController extends Controller
{
    public function sitemap(): void
    {
        $blogEntries = $this->loadBlogEntries();
        $servicioEntries = $this->loadServicioEntries();

        $entries = [];
        $entries[] = $this->buildEntry(Url::to('/'), $this->loadHomeLastModified($blogEntries), 'daily', '1.0');
        $entries[] = $this->buildEntry(Url::to('/blog'), $this->latestLastMod($blogEntries), 'daily', '0.9');
        $entries[] = $this->buildEntry(Url::to('/contacto'), null, 'monthly', '0.7');

        foreach (array_merge($servicioEntries, $blogEntries) as $entry) {
            $entries[] = $entry;
        }

        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noarchive');

        echo $this->renderSitemapXml($entries);
    }

    public function robots(): void
    {
        $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($isDebug) {
            $lines = [
                'User-agent: *',
                'Disallow: /',
            ];
            header('Content-Type: text/plain; charset=UTF-8');
            echo implode("\n", $lines) . "\n";
            return;
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            // /login, /forgot-password y /reset-password no se bloquean aqui: llevan
            // <meta name="robots" content="noindex">, que Google solo ve si puede rastrearlas.
            'Disallow: /dashboard',
            'Disallow: /logout',
            'Disallow: /usuarios',
            'Disallow: /grupos',
            'Disallow: /personas',
            'Disallow: /modulos',
            'Disallow: /tareas',
            'Disallow: /slider',
            'Disallow: /redes',
            'Disallow: /blogs',
            'Disallow: /contenidos',
            'Disallow: /galerias',
            'Disallow: /servicios',
            'Disallow: /testimonios',
            'Disallow: /notificaciones',
            'Disallow: /logs',
            'Disallow: /parametros',
            'Sitemap: ' . Url::to('/sitemap.xml'),
        ];

        header('Content-Type: text/plain; charset=UTF-8');
        echo implode("\n", $lines) . "\n";
    }

    /** @return array<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> */
    private function loadBlogEntries(): array
    {
        try {
            $model = new BlogModel();
            $total = $model->countPublished();
            if ($total < 1) {
                return [];
            }

            $rows = $model->paginatePublished(0, $total);
            $items = [];
            foreach ($rows as $row) {
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $items[] = $this->buildEntry(
                    Url::to('/blog/' . rawurlencode($slug)),
                    $this->normalizeDate((string) ($row['modified'] ?? ''), (string) ($row['created'] ?? '')),
                    'weekly',
                    '0.8'
                );
            }

            return $items;
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> */
    private function loadServicioEntries(): array
    {
        try {
            $items = [];
            foreach ((new ServicioModel())->getPublishedForSitemap() as $row) {
                $items[] = $this->buildEntry(
                    Url::to('/servicio/' . rawurlencode((string) $row['slug'])),
                    $this->normalizeDate((string) ($row['modified'] ?? ''), (string) ($row['created'] ?? '')),
                    'monthly',
                    '0.8'
                );
            }

            return $items;
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array{loc:string,lastmod:?string,changefreq:string,priority:string} */
    private function buildEntry(string $loc, ?string $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    /** @param array<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> $entries */
    private function latestLastMod(array $entries): ?string
    {
        $latestTs = null;
        $latestIso = null;

        foreach ($entries as $entry) {
            $value = (string) ($entry['lastmod'] ?? '');
            if ($value === '') {
                continue;
            }

            $ts = strtotime($value);
            if ($ts === false) {
                continue;
            }

            if ($latestTs === null || $ts > $latestTs) {
                $latestTs = $ts;
                $latestIso = date('c', $ts);
            }
        }

        return $latestIso;
    }

    private function normalizeDate(string ...$values): ?string
    {
        foreach ($values as $value) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }

            $ts = strtotime($value);
            if ($ts === false) {
                continue;
            }

            return date('c', $ts);
        }

        return null;
    }

    /** @param array<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> $blogEntries */
    private function loadHomeLastModified(array $blogEntries): ?string
    {
        return $this->normalizeDate(
            $this->loadSliderLastModified() ?? '',
            $this->latestLastMod($blogEntries) ?? ''
        );
    }

    private function loadSliderLastModified(): ?string
    {
        try {
            return (new SliderModel())->latestActiveTimestamp();
        } catch (Throwable) {
            return null;
        }
    }

    /** @param array<int, array{loc:string,lastmod:?string,changefreq:string,priority:string}> $entries */
    private function renderSitemapXml(array $entries): string
    {
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($entries as $entry) {
            $loc = $this->escapeXml((string) ($entry['loc'] ?? ''));
            if ($loc === '') {
                continue;
            }

            $xml[] = '  <url>';
            $xml[] = '    <loc>' . $loc . '</loc>';

            $lastmod = trim((string) ($entry['lastmod'] ?? ''));
            if ($lastmod !== '') {
                $xml[] = '    <lastmod>' . $this->escapeXml($lastmod) . '</lastmod>';
            }

            $changefreq = trim((string) ($entry['changefreq'] ?? ''));
            if ($changefreq !== '') {
                $xml[] = '    <changefreq>' . $this->escapeXml($changefreq) . '</changefreq>';
            }

            $priority = trim((string) ($entry['priority'] ?? ''));
            if ($priority !== '') {
                $xml[] = '    <priority>' . $this->escapeXml($priority) . '</priority>';
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml) . "\n";
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
