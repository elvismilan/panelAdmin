<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class ContenidosModel extends Model implements SearchablePaginated
{
    private string $tableContenidos;

    public function __construct()
    {
        parent::__construct();
        $this->tableContenidos = $this->tableName('contenidos');
        $this->setTable('contenidos');
        $this->setPrimaryKey('id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT id, titulo, subtitle, contenido, imagen, lugar, link, estado, created, modified
                FROM {$this->tableContenidos}
                {$where}
                ORDER BY id DESC
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

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->tableContenidos} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, titulo, subtitle, contenido, imagen, lugar, link, estado, created, modified
                FROM {$this->tableContenidos}
                WHERE id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function findActiveById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT id, titulo, subtitle, contenido, imagen, lugar, link, estado, created, modified
                FROM {$this->tableContenidos}
                WHERE id = :id
                  AND estado = :estado
                LIMIT 1";

        $row = $this->db->query($sql, [
            'id' => $id,
            'estado' => 'H',
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    public function findActiveByLugar(string $lugar): ?array
    {
        $lugar = trim($lugar);
        if ($lugar === '') {
            return null;
        }

        $sql = "SELECT id, titulo, subtitle, contenido, imagen, lugar, link, estado, created, modified
                FROM {$this->tableContenidos}
                WHERE lugar = :lugar
                  AND estado = :estado
                ORDER BY id DESC
                LIMIT 1";

        $row = $this->db->query($sql, [
            'lugar' => $lugar,
            'estado' => 'H',
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    /** @return array<int, array<string,mixed>> */
    public function findAllActiveByLugar(string $lugar, int $limit = 0): array
    {
        $lugar = trim($lugar);
        if ($lugar === '') {
            return [];
        }

        $sql = "SELECT id, titulo, subtitle, contenido, imagen, lugar, link, estado, created, modified
                FROM {$this->tableContenidos}
                WHERE lugar = :lugar
                  AND estado = :estado
                ORDER BY id ASC";

        $params = [
            'lugar' => $lugar,
            'estado' => 'H',
        ];

        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    public function createRecord(array $data): string
    {
        return $this->create([
            'titulo' => trim((string) ($data['titulo'] ?? '')),
            'subtitle' => ($data['subtitle'] ?? '') !== '' ? trim((string) $data['subtitle']) : null,
            'contenido' => trim((string) ($data['contenido'] ?? '')),
            'imagen' => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
            'lugar' => ($data['lugar'] ?? '') !== '' ? trim((string) $data['lugar']) : null,
            'link' => ($data['link'] ?? '') !== '' ? trim((string) $data['link']) : null,
            'estado' => trim((string) ($data['estado'] ?? 'H')),
            'created' => date('Y-m-d H:i:s'),
            'modified' => null,
        ]);
    }

    public function updateRecord(string $id, array $data): bool
    {
        return $this->update($id, [
            'titulo' => trim((string) ($data['titulo'] ?? '')),
            'subtitle' => ($data['subtitle'] ?? '') !== '' ? trim((string) $data['subtitle']) : null,
            'contenido' => trim((string) ($data['contenido'] ?? '')),
            'imagen' => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
            'lugar' => ($data['lugar'] ?? '') !== '' ? trim((string) $data['lugar']) : null,
            'link' => ($data['link'] ?? '') !== '' ? trim((string) $data['link']) : null,
            'estado' => trim((string) ($data['estado'] ?? 'H')),
            'modified' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->delete($id);
    }

    public function latestActiveTimestamp(array $ids = [], array $lugares = []): ?string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0));
        $lugares = array_values(array_filter(array_map(static fn($value): string => trim((string) $value), $lugares), static fn(string $value): bool => $value !== ''));

        if ($ids === [] && $lugares === []) {
            return null;
        }

        $params = ['estado' => 'H'];
        $filters = [];

        if ($ids !== []) {
            $placeholders = [];
            foreach ($ids as $index => $id) {
                $key = 'id_' . $index;
                $params[$key] = $id;
                $placeholders[] = ':' . $key;
            }
            $filters[] = 'id IN (' . implode(', ', $placeholders) . ')';
        }

        if ($lugares !== []) {
            $placeholders = [];
            foreach ($lugares as $index => $lugar) {
                $key = 'lugar_' . $index;
                $params[$key] = $lugar;
                $placeholders[] = ':' . $key;
            }
            $filters[] = 'lugar IN (' . implode(', ', $placeholders) . ')';
        }

        $where = $filters !== []
            ? 'AND (' . implode(' OR ', $filters) . ')'
            : '';

        $sql = "SELECT modified, created
                FROM {$this->tableContenidos}
                WHERE estado = :estado
                  {$where}
                ORDER BY COALESCE(modified, created) DESC, id DESC
                LIMIT 1";

        $row = $this->db->query($sql, $params)->fetch();
        if (!is_array($row)) {
            return null;
        }

        $modified = trim((string) ($row['modified'] ?? ''));
        if ($modified !== '') {
            return $modified;
        }

        $created = trim((string) ($row['created'] ?? ''));
        return $created !== '' ? $created : null;
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(titulo LIKE :search OR subtitle LIKE :search2 OR contenido LIKE :search3 OR lugar LIKE :search4 OR link LIKE :search5)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
            $params['search4'] = '%' . $search . '%';
            $params['search5'] = '%' . $search . '%';
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }
}
