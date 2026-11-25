<?php

namespace App\Models;

use PanelAdmin\Core\Model;

/**
 * Categorias (`categorias`) y su relacion con otros registros
 * (`categoria_relacion`), separadas por tipo (ej. 'blog').
 */
class CategoriaModel extends Model
{
    private string $categoriesTable;
    private string $categoryRelationTable;

    public function __construct()
    {
        parent::__construct();
        $this->categoriesTable = $this->tableName('categorias');
        $this->categoryRelationTable = $this->tableName('categoria_relacion');
        $this->setTable('categorias');
        $this->setPrimaryKey('cat_id');
    }

    public function getCategoriesByType(string $type, bool $onlyEnabled = false): array
    {
        $sql = "SELECT cat_id, cat_name, cat_slug, cat_status
                FROM {$this->categoriesTable}
                WHERE cat_tipo = :tipo";

        if ($onlyEnabled) {
            $sql .= " AND cat_status = 'H'";
        }

        $sql .= " ORDER BY cat_name ASC";

        try {
            $rows = $this->db->query($sql, ['tipo' => trim($type)])->fetchAll();
        } catch (\Throwable) {
            return [];
        }

        return $rows;
    }

    public function getActiveByType(string $type): array
    {
        return $this->getCategoriesByType($type, true);
    }

    public function getIdsByRelation(string $relType, int $relId): array
    {
        $sql = "SELECT categoria_id
                FROM {$this->categoryRelationTable}
                WHERE rel_tipo = :tipo AND rel_id = :rel_id
                ORDER BY id ASC";

        try {
            $rows = $this->db->query($sql, [
                'tipo' => $relType,
                'rel_id' => $relId,
            ])->fetchAll();
        } catch (\Throwable) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['categoria_id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    public function filterValidIds(array $ids, string $type, bool $onlyEnabled = true): array
    {
        $normalized = self::normalizeIds($ids);
        if ($normalized === []) {
            return [];
        }

        $placeholders = [];
        $params = ['tipo' => $type];

        foreach ($normalized as $i => $id) {
            $key = 'id_' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = "SELECT cat_id
                FROM {$this->categoriesTable}
                WHERE cat_id IN (" . implode(', ', $placeholders) . ")
                  AND cat_tipo = :tipo";

        if ($onlyEnabled) {
            $sql .= " AND cat_status = 'H'";
        }

        $rows = $this->db->query($sql, $params)->fetchAll();
        $validSet = [];
        foreach ($rows as $row) {
            $validId = (int) ($row['cat_id'] ?? 0);
            if ($validId > 0) {
                $validSet[$validId] = true;
            }
        }

        $filtered = [];
        foreach ($normalized as $id) {
            if (isset($validSet[$id])) {
                $filtered[] = $id;
            }
        }

        return array_values(array_unique($filtered));
    }

    public function existsNameByType(string $name, string $type, ?int $excludeId = null): bool
    {
        $name = trim($name);
        $type = trim($type);
        if ($name === '' || $type === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM {$this->categoriesTable}
                WHERE LOWER(TRIM(cat_name)) = LOWER(TRIM(:name))
                  AND cat_tipo = :tipo";
        $params = ['name' => $name, 'tipo' => $type];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND cat_id <> :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }

    public function existsSlugByType(string $slug, string $type, ?int $excludeId = null): bool
    {
        $slug = trim($slug);
        $type = trim($type);
        if ($slug === '' || $type === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM {$this->categoriesTable}
                WHERE LOWER(TRIM(cat_slug)) = LOWER(TRIM(:slug))
                  AND cat_tipo = :tipo";
        $params = ['slug' => $slug, 'tipo' => $type];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= " AND cat_id <> :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }

    public function createCategory(string $name, string $slug, string $type): string
    {
        $payload = [
            'cat_name' => trim($name),
            'cat_slug' => trim($slug),
            'cat_parent' => 0,
            'cat_status' => 'H',
            'cat_created' => date('Y-m-d H:i:s'),
            'cat_tipo' => trim($type),
        ];

        $fields = implode(', ', array_keys($payload));
        $values = ':' . implode(', :', array_keys($payload));
        $sql = "INSERT INTO {$this->categoriesTable} ({$fields}) VALUES ({$values})";
        $this->db->query($sql, $payload);

        return $this->db->lastInsertId();
    }

    public function findByIdForType(int $id, string $type): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT cat_id, cat_name, cat_slug, cat_status
                FROM {$this->categoriesTable}
                WHERE cat_id = :id
                  AND cat_tipo = :tipo
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id, 'tipo' => trim($type)])->fetch();

        return is_array($row) ? $row : null;
    }

    public function countRelations(int $categoryId, string $relType): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM {$this->categoryRelationTable}
             WHERE categoria_id = :categoria_id
               AND rel_tipo = :rel_tipo",
            [
                'categoria_id' => $categoryId,
                'rel_tipo' => $relType,
            ]
        )->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function deleteCategory(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $this->db->query("DELETE FROM {$this->categoriesTable} WHERE cat_id = :id", ['id' => $id]);
        return true;
    }

    public function syncRelation(string $relType, int $relId, array $categoryIds): void
    {
        $this->db->query(
            "DELETE FROM {$this->categoryRelationTable} WHERE rel_tipo = :tipo AND rel_id = :rel_id",
            ['tipo' => $relType, 'rel_id' => $relId]
        );

        if ($categoryIds === []) {
            return;
        }

        $sql = "INSERT INTO {$this->categoryRelationTable} (categoria_id, rel_tipo, rel_id, created)
                VALUES (:categoria_id, :rel_tipo, :rel_id, :created)";

        foreach ($categoryIds as $categoryId) {
            $this->db->query($sql, [
                'categoria_id' => $categoryId,
                'rel_tipo' => $relType,
                'rel_id' => $relId,
                'created' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function deleteRelations(string $relType, int $relId): void
    {
        $this->db->query(
            "DELETE FROM {$this->categoryRelationTable} WHERE rel_tipo = :tipo AND rel_id = :rel_id",
            ['tipo' => $relType, 'rel_id' => $relId]
        );
    }

    /**
     * Columna calculada `category_names_raw` (nombres separados por '||') para
     * agregar al SELECT de un listado; espera la tabla principal con alias `p`.
     */
    public function namesSubquery(string $type): string
    {
        $quotedType = $this->db->getConnection()->quote($type);
        $typeFilter = ' AND c.cat_tipo = ' . $quotedType;

        return ", (
            SELECT GROUP_CONCAT(c.cat_name ORDER BY c.cat_name SEPARATOR '||')
            FROM {$this->categoryRelationTable} cr
            INNER JOIN {$this->categoriesTable} c ON c.cat_id = cr.categoria_id
            WHERE cr.rel_tipo = " . $quotedType . "
              AND cr.rel_id = p.id{$typeFilter}
        ) AS category_names_raw";
    }

    /** @return int[] */
    public static function normalizeIds(mixed $raw): array
    {
        $values = is_array($raw) ? $raw : [$raw];
        $normalized = [];

        foreach ($values as $value) {
            $id = (int) trim((string) $value);
            if ($id > 0) {
                $normalized[] = $id;
            }
        }

        return array_values(array_unique($normalized));
    }
}
