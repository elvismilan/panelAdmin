<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class GaleriaModel extends Model implements SearchablePaginated
{
    private string $galeriaTable;
    private string $fotoTable;
    private string $lastActionLog = '';

    public function __construct()
    {
        parent::__construct();
        $this->galeriaTable = $this->tableName('galeria');
        $this->fotoTable = $this->tableName('galeria_foto');
        $this->setTable('galeria');
        $this->setPrimaryKey('id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT g.id, g.nombre, g.slug, g.resumen, g.fecha, g.portada, g.estado,
                       (SELECT COUNT(*) FROM {$this->fotoTable} f WHERE f.igf_gal_id = g.id) AS total_fotos
                FROM {$this->galeriaTable} g
                {$where}
                ORDER BY g.id DESC
                LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->galeriaTable} g {$where}";

        return $this->countByQuery($sql, $params);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, nombre, slug, resumen, fecha, portada, estado
                FROM {$this->galeriaTable}
                WHERE id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createRecord(array $data): string
    {
        $nombre = trim((string) ($data['nombre'] ?? ''));

        $id = $this->create([
            'nombre' => $nombre,
            'slug' => trim((string) ($data['slug'] ?? '')),
            'resumen' => (string) ($data['resumen'] ?? ''),
            'fecha' => ($data['fecha'] ?? '') !== '' ? (string) $data['fecha'] : null,
            'portada' => trim((string) ($data['portada'] ?? '')),
            // Columnas polimorficas legacy: las galerias del CMS son independientes.
            'tabla_id' => 0,
            'tabla' => '',
            'estado' => (string) ($data['estado'] ?? 'H'),
            'created' => date('Y-m-d H:i:s'),
        ]);

        $this->lastActionLog = "Galeria creada: {$nombre} (ID {$id})";

        return $id;
    }

    public function updateRecord(string $id, array $data): bool
    {
        $nombre = trim((string) ($data['nombre'] ?? ''));

        $updated = $this->update($id, [
            'nombre' => $nombre,
            'slug' => trim((string) ($data['slug'] ?? '')),
            'resumen' => (string) ($data['resumen'] ?? ''),
            'fecha' => ($data['fecha'] ?? '') !== '' ? (string) $data['fecha'] : null,
            'portada' => trim((string) ($data['portada'] ?? '')),
            'estado' => (string) ($data['estado'] ?? 'H'),
        ]);

        $this->lastActionLog = "Galeria actualizada: {$nombre} (ID {$id})";

        return $updated;
    }

    public function deleteRecord(string $id): bool
    {
        $this->db->query(
            "DELETE FROM {$this->fotoTable} WHERE igf_gal_id = :id",
            ['id' => $id]
        );

        $deleted = $this->delete($id);
        $this->lastActionLog = "Galeria eliminada (ID {$id})";

        return $deleted;
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->galeriaTable,
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

    // ------------------------------------------------------------------ Sitio publico

    public function countPublishedFotos(): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->fotoTable} f
                INNER JOIN {$this->galeriaTable} g ON g.id = f.igf_gal_id
                WHERE g.estado = 'H'";

        return $this->countByQuery($sql);
    }

    /** @return array<int, array<string, mixed>> */
    public function paginatePublishedFotos(int $offset, int $limit): array
    {
        $sql = "SELECT f.igf_id, f.igf_image, f.igf_alt, f.created,
                       g.id AS galeria_id, g.nombre AS galeria_nombre, g.slug AS galeria_slug
                FROM {$this->fotoTable} f
                INNER JOIN {$this->galeriaTable} g ON g.id = f.igf_gal_id
                WHERE g.estado = 'H'
                ORDER BY f.igf_id DESC
                LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, [], $offset, $limit);
    }

    public function latestPublishedFotoTimestamp(): ?string
    {
        $sql = "SELECT f.created
                FROM {$this->fotoTable} f
                INNER JOIN {$this->galeriaTable} g ON g.id = f.igf_gal_id
                WHERE g.estado = 'H'
                ORDER BY f.created DESC, f.igf_id DESC
                LIMIT 1";

        $row = $this->db->query($sql)->fetch();
        $created = trim((string) ($row['created'] ?? ''));

        return $created !== '' ? $created : null;
    }

    // ------------------------------------------------------------------ Fotos

    /** @return array<int, array<string, mixed>> */
    public function fotosByGaleria(string $galeriaId): array
    {
        $sql = "SELECT igf_id, igf_image, igf_alt, created
                FROM {$this->fotoTable}
                WHERE igf_gal_id = :gal
                ORDER BY igf_id DESC";

        return $this->db->query($sql, ['gal' => $galeriaId])->fetchAll();
    }

    public function countFotos(string $galeriaId): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->fotoTable} WHERE igf_gal_id = :gal";

        return $this->countByQuery($sql, ['gal' => $galeriaId]);
    }

    public function createFoto(string $galeriaId, string $image, string $alt): string
    {
        $this->db->query(
            "INSERT INTO {$this->fotoTable} (igf_image, igf_gal_id, igf_alt, created)
             VALUES (:imagen, :gal, :alt, :creado)",
            [
                'imagen' => $image,
                'gal' => $galeriaId,
                'alt' => $alt,
                'creado' => date('Y-m-d H:i:s'),
            ]
        );

        $id = $this->db->lastInsertId();
        $this->lastActionLog = "Foto agregada a galeria {$galeriaId}: {$alt} (ID {$id})";

        return $id;
    }

    public function findFotoById(string $fotoId, string $galeriaId): ?array
    {
        $sql = "SELECT igf_id, igf_image, igf_alt, igf_gal_id
                FROM {$this->fotoTable}
                WHERE igf_id = :id AND igf_gal_id = :gal
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $fotoId, 'gal' => $galeriaId])->fetch();
        return is_array($row) ? $row : null;
    }

    public function deleteFoto(string $fotoId, string $galeriaId): bool
    {
        $stmt = $this->db->query(
            "DELETE FROM {$this->fotoTable} WHERE igf_id = :id AND igf_gal_id = :gal",
            ['id' => $fotoId, 'gal' => $galeriaId]
        );

        $this->lastActionLog = "Foto eliminada (ID {$fotoId}) de galeria {$galeriaId}";

        return $stmt->rowCount() > 0;
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(g.nombre LIKE :search1 OR g.slug LIKE :search2)';
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
