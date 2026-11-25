<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class BlogsModel extends Model implements SearchablePaginated
{
    private const BLOG_CATEGORY_TYPE = 'blog';

    private string $postsTable;
    private CategoriaModel $categorias;

    public function __construct()
    {
        parent::__construct();
        $this->postsTable = $this->tableName('posts');
        $this->categorias = new CategoriaModel();
        $this->setTable('posts');
        $this->setPrimaryKey('id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);
        $categorySelectSql = $this->categorias->namesSubquery(self::BLOG_CATEGORY_TYPE);

        $sql = "SELECT p.id, p.titulo, p.slug, p.contenido, p.detalle, p.imagen, p.autor, p.vistas, p.estado, p.created, p.modified{$categorySelectSql}
                FROM {$this->postsTable} p
                {$where}
                ORDER BY p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Posts publicados mas recientes, para vitrinas del sitio publico (ej. Home).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentPublished(int $limit = 3): array
    {
        $safeLimit = max(1, min($limit, 20));

        $sql = "SELECT id, titulo, slug, detalle, imagen, created
                FROM {$this->postsTable}
                WHERE estado = 'H'
                ORDER BY created DESC, id DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $safeLimit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->postsTable} p {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT p.id, p.titulo, p.slug, p.contenido, p.detalle, p.imagen, p.autor, p.vistas, p.estado, p.created, p.modified
                FROM {$this->postsTable} p
                WHERE p.id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();

        return is_array($row) ? $row : null;
    }

    public function createRecord(array $data): string
    {
        $categoryIds = CategoriaModel::normalizeIds($data['post_category_ids'] ?? []);

        $payload = [
            'titulo' => trim((string) ($data['titulo'] ?? '')),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'contenido' => trim((string) ($data['contenido'] ?? '')),
            'detalle' => ($data['detalle'] ?? '') !== '' ? trim((string) $data['detalle']) : null,
            'imagen' => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
            'autor' => ($data['autor'] ?? '') !== '' ? trim((string) $data['autor']) : null,
            'vistas' => max(1, (int) ($data['vistas'] ?? 1)),
            'estado' => trim((string) ($data['estado'] ?? 'H')),
            'created' => date('Y-m-d H:i:s'),
            'modified' => null,
        ];

        $this->db->beginTransaction();
        try {
            $id = $this->create($payload);
            $this->categorias->syncRelation(self::BLOG_CATEGORY_TYPE, (int) $id, $categoryIds);
            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateRecord(string $id, array $data): bool
    {
        $categoryIds = CategoriaModel::normalizeIds($data['post_category_ids'] ?? []);

        $payload = [
            'titulo' => trim((string) ($data['titulo'] ?? '')),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'contenido' => trim((string) ($data['contenido'] ?? '')),
            'detalle' => ($data['detalle'] ?? '') !== '' ? trim((string) $data['detalle']) : null,
            'imagen' => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
            'autor' => ($data['autor'] ?? '') !== '' ? trim((string) $data['autor']) : null,
            'estado' => trim((string) ($data['estado'] ?? 'H')),
            'modified' => date('Y-m-d H:i:s'),
        ];

        $this->db->beginTransaction();
        try {
            $this->update($id, $payload);
            $this->categorias->syncRelation(self::BLOG_CATEGORY_TYPE, (int) $id, $categoryIds);
            $this->db->commit();

            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteRecord(string $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->categorias->deleteRelations(self::BLOG_CATEGORY_TYPE, (int) $id);
            $result = $this->delete($id);
            $this->db->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function existsBySlug(string $slug, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->postsTable, 'slug', trim($slug), 'id', $excludeId);
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(p.titulo LIKE :search OR p.slug LIKE :search2 OR p.autor LIKE :search3 OR p.detalle LIKE :search4)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
            $params['search4'] = '%' . $search . '%';
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }
}
