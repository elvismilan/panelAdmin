<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class ServicioModel extends Model implements SearchablePaginated
{
    private string $servicioTable;
    private string $lastActionLog = '';

    public function __construct()
    {
        parent::__construct();
        $this->servicioTable = $this->tableName('servicios');
        $this->setTable('servicios');
        $this->setPrimaryKey('id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT id, titulo, slug, imagen, icon, vistas, estado
                FROM {$this->servicioTable}
                {$where}
                ORDER BY id DESC
                LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->servicioTable} {$where}";

        return $this->countByQuery($sql, $params);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, titulo, subtitulo, slug, contenido, imagen, icon, vistas, estado
                FROM {$this->servicioTable}
                WHERE id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createRecord(array $data): string
    {
        $titulo = trim((string) ($data['titulo'] ?? ''));
        $now = date('Y-m-d H:i:s');

        $id = $this->create([
            'titulo' => $titulo,
            'subtitulo' => trim((string) ($data['subtitulo'] ?? '')) !== '' ? trim((string) $data['subtitulo']) : null,
            'slug' => trim((string) ($data['slug'] ?? '')),
            'contenido' => (string) ($data['contenido'] ?? ''),
            'imagen' => trim((string) ($data['imagen'] ?? '')),
            'icon' => ($data['icon'] ?? '') !== '' ? trim((string) $data['icon']) : null,
            // Columna legacy no usada por el CMS actual.
            'detalle' => '',
            'vistas' => 0,
            'estado' => (string) ($data['estado'] ?? 'H'),
            'created' => $now,
            'modified' => $now,
        ]);

        $this->lastActionLog = "Servicio creado: {$titulo} (ID {$id})";

        return $id;
    }

    public function updateRecord(string $id, array $data): bool
    {
        $titulo = trim((string) ($data['titulo'] ?? ''));

        $updated = $this->update($id, [
            'titulo' => $titulo,
            'subtitulo' => trim((string) ($data['subtitulo'] ?? '')) !== '' ? trim((string) $data['subtitulo']) : null,
            'slug' => trim((string) ($data['slug'] ?? '')),
            'contenido' => (string) ($data['contenido'] ?? ''),
            'imagen' => trim((string) ($data['imagen'] ?? '')),
            'icon' => ($data['icon'] ?? '') !== '' ? trim((string) $data['icon']) : null,
            'estado' => (string) ($data['estado'] ?? 'H'),
            'modified' => date('Y-m-d H:i:s'),
        ]);

        $this->lastActionLog = "Servicio actualizado: {$titulo} (ID {$id})";

        return $updated;
    }

    public function deleteRecord(string $id): bool
    {
        $deleted = $this->delete($id);
        $this->lastActionLog = "Servicio eliminado (ID {$id})";

        return $deleted;
    }

    /** @return array<int, array<string, mixed>> */
    public function getActiveForHome(int $limit = 10): array
    {
        $safeLimit = max(1, min($limit, 50));

        $sql = "SELECT id, titulo, subtitulo, slug, imagen, icon
                FROM {$this->servicioTable}
                WHERE estado = 'H'
                ORDER BY id DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $safeLimit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Servicios publicados (slug y fechas) para el sitemap.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPublishedForSitemap(): array
    {
        $sql = "SELECT slug, created, modified
                FROM {$this->servicioTable}
                WHERE estado = 'H' AND slug <> ''
                ORDER BY id ASC";

        return $this->db->query($sql)->fetchAll() ?: [];
    }

    public function findPublicBySlug(string $slug): ?array
    {
        $sql = "SELECT id, titulo, subtitulo, slug, contenido, imagen, icon, vistas, estado
                FROM {$this->servicioTable}
                WHERE slug = :slug AND estado = 'H'
                LIMIT 1";

        $row = $this->db->query($sql, ['slug' => $slug])->fetch();
        return is_array($row) ? $row : null;
    }

    public function incrementViews(string $servicioId): void
    {
        $this->incrementCounterColumn(
            $this->servicioTable,
            'id',
            max(0, (int) $servicioId),
            'vistas',
            ['estado' => 'H']
        );
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->servicioTable,
            'slug',
            trim($slug),
            'id',
            $excludeId
        );
    }

    public function getLastActionLog(): string
    {
        return $this->lastActionLog !== '' ? $this->lastActionLog : parent::getLastActionLog();
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(titulo LIKE :search1 OR slug LIKE :search2)';
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
