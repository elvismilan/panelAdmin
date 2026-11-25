<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class TestimonioModel extends Model implements SearchablePaginated
{
    protected bool $softDeletes = true;
    protected string $deletedAtColumn = 'deleted';

    private string $testimonioTable;
    private string $lastActionLog = '';

    public function __construct()
    {
        parent::__construct();
        $this->testimonioTable = $this->tableName('testimonios');
        $this->setTable('testimonios');
        $this->setPrimaryKey('id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT id, nombre, contexto, imagen, detalle, orden, estado
                FROM {$this->testimonioTable}
                {$where}
                ORDER BY orden ASC, id ASC
                LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->testimonioTable} {$where}";

        return $this->countByQuery($sql, $params);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, nombre, contexto, imagen, detalle, orden, estado
                FROM {$this->testimonioTable}
                WHERE id = :id AND deleted IS NULL
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    /**
     * Testimonios visibles en el sitio publico (habilitados y sin borrar), en
     * el orden del admin. Alimenta components/testimonios.php.
     * Si la base falla devuelve [] (el bloque no se muestra) y deja el error en el log.
     *
     * @return list<array{id: int, nombre: string, contexto: string, detalle: string}>
     */
    public function getPublished(int $limit = 0): array
    {
        $sql = "SELECT id, nombre, contexto, detalle
                FROM {$this->testimonioTable}
                WHERE estado = 'H' AND deleted IS NULL
                ORDER BY orden ASC, id ASC";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        try {
            $rows = $this->db->query($sql)->fetchAll();
        } catch (\Throwable $e) {
            error_log('TestimonioModel::getPublished error: ' . $e->getMessage());
            return [];
        }

        return self::cleanPublished($rows);
    }

    /**
     * Recorta nombre y detalle y descarta los testimonios sin texto.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return list<array{id: int, nombre: string, contexto: string, detalle: string}>
     */
    public static function cleanPublished(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $detalle = trim((string) ($row['detalle'] ?? ''));
            if ($detalle === '') {
                continue;
            }
            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'nombre' => trim((string) ($row['nombre'] ?? '')),
                'contexto' => trim((string) ($row['contexto'] ?? '')),
                'detalle' => $detalle,
            ];
        }

        return $items;
    }

    public function createRecord(array $data): string
    {
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $now = date('Y-m-d H:i:s');

        $id = $this->create([
            'nombre' => $nombre,
            'contexto' => $this->nullableText($data['contexto'] ?? null),
            'detalle' => trim((string) ($data['detalle'] ?? '')),
            'orden' => $this->nextOrden(),
            'estado' => (string) ($data['estado'] ?? 'H'),
            'created' => $now,
            'modified' => $now,
        ]);

        $this->lastActionLog = "Testimonio creado: {$nombre} (ID {$id})";

        return $id;
    }

    public function updateRecord(string $id, array $data): bool
    {
        $nombre = trim((string) ($data['nombre'] ?? ''));

        // Sin foto (decision de Elvis, 17/11): `imagen` queda en la tabla sin uso.
        $updated = $this->update($id, [
            'nombre' => $nombre,
            'contexto' => $this->nullableText($data['contexto'] ?? null),
            'detalle' => trim((string) ($data['detalle'] ?? '')),
            'estado' => (string) ($data['estado'] ?? 'H'),
            'modified' => date('Y-m-d H:i:s'),
        ]);

        $this->lastActionLog = "Testimonio actualizado: {$nombre} (ID {$id})";

        return $updated;
    }

    public function deleteRecord(string $id): bool
    {
        $deleted = $this->delete($id);
        $this->lastActionLog = "Testimonio eliminado (ID {$id})";

        return $deleted;
    }

    public function getLastActionLog(): string
    {
        return $this->lastActionLog !== '' ? $this->lastActionLog : parent::getLastActionLog();
    }

    /**
     * Mueve un registro una posicion arriba o abajo dentro del orden global
     * y resecuencia todos los ordenes (1..n) para evitar duplicados y huecos.
     *
     * @return array<string, int>|null Mapa id => orden nuevo de los registros
     *                                 afectados, o null si no hubo cambio.
     */
    public function moveRecord(string $id, string $direction): ?array
    {
        $result = $this->moveByOrden($this->testimonioTable, 'id', 'orden', $id, $direction, 'deleted IS NULL');

        if ($result !== null) {
            $this->lastActionLog = "Testimonio reordenado (ID {$id}, {$direction})";
        }

        return $result;
    }

    /** Texto opcional: recortado, o null si viene vacio. */
    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : null;
    }

    /** Orden de un testimonio nuevo: el siguiente al maximo. Al editar no se toca (se cambia con las flechas). */
    private function nextOrden(): int
    {
        $sql = "SELECT COALESCE(MAX(orden), 0) AS total
                FROM {$this->testimonioTable}
                WHERE deleted IS NULL";
        $row = $this->db->query($sql)->fetch();

        return min(65535, (int) ($row['total'] ?? 0) + 1);
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = ['deleted IS NULL'];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(nombre LIKE :search1 OR detalle LIKE :search2 OR contexto LIKE :search3)';
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
            $params['search3'] = $pattern;
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }
}
