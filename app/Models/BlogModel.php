<?php

namespace App\Models;

use PanelAdmin\Core\Model;

class BlogModel extends Model
{
    private const BLOG_CATEGORY_TYPE = 'blog';

    private string $postsTable;
    private string $categoriesTable;
    private string $categoryRelationTable;

    public function __construct()
    {
        parent::__construct();
        $this->postsTable = $this->tableName('posts');
        $this->categoriesTable = $this->tableName('categorias');
        $this->categoryRelationTable = $this->tableName('categoria_relacion');
    }

    public function countPublished(?int $categoryId = null): int
    {
        $filter = $this->buildCategoryFilterSql($categoryId);
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->postsTable} p
                WHERE p.estado = :status{$filter['sql']}";

        $params = array_merge(['status' => 'H'], $filter['params']);
        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function paginatePublished(int $offset, int $limit, ?int $categoryId = null): array
    {
        $categorySelectSql = $this->categoryNamesSelectSql();
        $filter = $this->buildCategoryFilterSql($categoryId);

        $sql = "SELECT p.id, p.titulo, p.slug, p.detalle, p.contenido, p.imagen, p.autor, p.created, p.modified{$categorySelectSql}
                FROM {$this->postsTable} p
                WHERE p.estado = :status{$filter['sql']}
                ORDER BY p.created DESC, p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':status', 'H', \PDO::PARAM_STR);
        foreach ($filter['params'] as $key => $value) {
            $stmt->bindValue(':' . $key, (int) $value, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function getLatestPublished(int $limit = 3, string $excludeId = ''): array
    {
        $categorySelectSql = $this->categoryNamesSelectSql();
        $exclude = max(0, (int) $excludeId);
        $excludeSql = $exclude > 0 ? ' AND p.id <> :exclude_id' : '';

        $sql = "SELECT p.id, p.titulo, p.slug, p.detalle, p.contenido, p.imagen, p.autor, p.created, p.modified{$categorySelectSql}
                FROM {$this->postsTable} p
                WHERE p.estado = :status{$excludeSql}
                ORDER BY p.created DESC, p.id DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':status', 'H', \PDO::PARAM_STR);
        if ($exclude > 0) {
            $stmt->bindValue(':exclude_id', $exclude, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $categorySelectSql = $this->categoryNamesSelectSql();

        $sql = "SELECT p.id, p.titulo, p.slug, p.detalle, p.contenido, p.imagen, p.autor, p.created, p.modified{$categorySelectSql}
                FROM {$this->postsTable} p
                WHERE p.estado = :status
                  AND p.slug = :slug
                LIMIT 1";

        $row = $this->db->query($sql, [
            'status' => 'H',
            'slug' => trim($slug),
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    public function incrementViews(string $postId): void
    {
        $this->incrementCounterColumn(
            $this->postsTable,
            'id',
            max(0, (int) $postId),
            'vistas',
            ['estado' => 'H']
        );
    }

    public function findCategoryBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $sql = "SELECT cat_id, cat_name, cat_slug
                FROM {$this->categoriesTable}
                WHERE cat_slug = :slug
                  AND cat_status = 'H'
                  AND cat_tipo = :cat_tipo
                LIMIT 1";

        $row = $this->db->query($sql, ['slug' => $slug, 'cat_tipo' => self::BLOG_CATEGORY_TYPE])->fetch();
        return is_array($row) ? $row : null;
    }

    public function findPreviousPublished(string $createdAt, string $excludeId): ?array
    {
        $categorySelectSql = $this->categoryNamesSelectSql();
        $exclude = max(0, (int) $excludeId);

        $sql = "SELECT p.id, p.titulo, p.slug, p.imagen, p.created{$categorySelectSql}
                FROM {$this->postsTable} p
                WHERE p.estado = :status
                  AND (p.created < :created OR (p.created = :created2 AND p.id < :exclude_id))
                ORDER BY p.created DESC, p.id DESC
                LIMIT 1";

        $row = $this->db->query($sql, [
            'status' => 'H',
            'created' => $createdAt,
            'created2' => $createdAt,
            'exclude_id' => $exclude,
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    public function findNextPublished(string $createdAt, string $excludeId): ?array
    {
        $categorySelectSql = $this->categoryNamesSelectSql();
        $exclude = max(0, (int) $excludeId);

        $sql = "SELECT p.id, p.titulo, p.slug, p.imagen, p.created{$categorySelectSql}
                FROM {$this->postsTable} p
                WHERE p.estado = :status
                  AND (p.created > :created OR (p.created = :created2 AND p.id > :exclude_id))
                ORDER BY p.created ASC, p.id ASC
                LIMIT 1";

        $row = $this->db->query($sql, [
            'status' => 'H',
            'created' => $createdAt,
            'created2' => $createdAt,
            'exclude_id' => $exclude,
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    public function getActiveCategories(int $limit = 0): array
    {
        $sql = "SELECT c.cat_id, c.cat_name, c.cat_slug, COUNT(DISTINCT p.id) AS total_posts
                FROM {$this->categoriesTable} c
                INNER JOIN {$this->categoryRelationTable} cr
                    ON cr.categoria_id = c.cat_id
                   AND cr.rel_tipo = :rel_tipo
                INNER JOIN {$this->postsTable} p
                    ON p.id = cr.rel_id
                   AND p.estado = :status
                WHERE c.cat_status = 'H' AND c.cat_tipo = :cat_tipo
                GROUP BY c.cat_id, c.cat_name, c.cat_slug
                HAVING COUNT(DISTINCT p.id) > 0
                ORDER BY total_posts DESC, c.cat_name ASC";

        $stmt = $this->db->getConnection()->prepare($sql . ($limit > 0 ? " LIMIT :limit" : ""));
        $stmt->bindValue(':rel_tipo', self::BLOG_CATEGORY_TYPE, \PDO::PARAM_STR);
        $stmt->bindValue(':status', 'H', \PDO::PARAM_STR);
        $stmt->bindValue(':cat_tipo', self::BLOG_CATEGORY_TYPE, \PDO::PARAM_STR);
        if ($limit > 0) {
            $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        }

        $stmt->execute();
        $rows = $stmt->fetchAll();
        return $rows;
    }

    private function categoryNamesSelectSql(): string
    {
        $typeFilter = " AND c.cat_tipo = '" . self::BLOG_CATEGORY_TYPE . "'";

        $namesSql = "(
            SELECT GROUP_CONCAT(c.cat_name ORDER BY c.cat_name SEPARATOR '||')
            FROM {$this->categoryRelationTable} cr
            INNER JOIN {$this->categoriesTable} c ON c.cat_id = cr.categoria_id
            WHERE cr.rel_tipo = '" . self::BLOG_CATEGORY_TYPE . "'
              AND cr.rel_id = p.id{$typeFilter}
        ) AS category_names_raw";

        $pairsSql = "(
            SELECT GROUP_CONCAT(CONCAT(c.cat_name, '::', c.cat_slug) ORDER BY c.cat_name SEPARATOR '||')
            FROM {$this->categoryRelationTable} cr
            INNER JOIN {$this->categoriesTable} c ON c.cat_id = cr.categoria_id
            WHERE cr.rel_tipo = '" . self::BLOG_CATEGORY_TYPE . "'
              AND cr.rel_id = p.id{$typeFilter}
        ) AS category_pairs_raw";

        return ', ' . $namesSql . ', ' . $pairsSql;
    }

    /** @return array{sql:string,params:array<string,int>} */
    private function buildCategoryFilterSql(?int $categoryId): array
    {
        $id = (int) ($categoryId ?? 0);
        if ($id <= 0) {
            return ['sql' => '', 'params' => []];
        }

        return [
            'sql' => " AND EXISTS (
                SELECT 1
                FROM {$this->categoryRelationTable} crf
                WHERE crf.rel_tipo = '" . self::BLOG_CATEGORY_TYPE . "'
                  AND crf.rel_id = p.id
                  AND crf.categoria_id = :filter_category_id
            )",
            'params' => ['filter_category_id' => $id],
        ];
    }
}
