<?php

namespace App\Models;

use PanelAdmin\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

class SliderModel extends Model
{
    private const ORDER_MIN = 1;
    private const ORDER_MAX = 255;

    private string $sliderTable;

    public function __construct()
    {
        parent::__construct();
        $this->sliderTable = $this->tableName('slider');
        $this->setTable('slider');
        $this->setPrimaryKey('id');
    }

    /**
     * Mueve un slide una posicion arriba o abajo y resecuencia el orden (1..n).
     *
     * @return array<string, int>|null Mapa id => orden nuevo, o null si no hubo cambio.
     */
    public function moveRecord(string $id, string $direction): ?array
    {
        return $this->moveByOrden($this->sliderTable, 'id', 'orden', $id, $direction);
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT id, titulo, subtitle, description, imagen, overlay, orden, estado, link_btn, nombre_btn, created, modified
                FROM {$this->sliderTable}
                {$where}
                ORDER BY orden ASC, id DESC
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

        $sql = "SELECT COUNT(*) AS total FROM {$this->sliderTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT id, titulo, subtitle, description, imagen, overlay, orden, estado, link_btn, nombre_btn, created, modified
                FROM {$this->sliderTable}
                WHERE id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function getActiveForHome(int $limit = 10): array
    {
        $safeLimit = max(1, min($limit, 50));

        $sql = "SELECT id, titulo, subtitle, description, imagen, overlay, orden, link_btn, nombre_btn
                FROM {$this->sliderTable}
                WHERE estado = 'H'
                ORDER BY orden ASC, id ASC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function latestActiveTimestamp(): ?string
    {
        $sql = "SELECT modified, created
                FROM {$this->sliderTable}
                WHERE estado = 'H'
                ORDER BY COALESCE(modified, created) DESC, id DESC
                LIMIT 1";

        $row = $this->db->query($sql)->fetch();
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

    public function createRecord(array $data): string
    {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();

        try {
            $requestedOrder = $this->parseOrder($data['orden'] ?? null);
            $maxOrder = $this->getMaxOrder($conn);
            $maxTarget = min(self::ORDER_MAX, $maxOrder + 1);

            if ($requestedOrder === null) {
                $resolvedOrder = max(self::ORDER_MIN, $maxTarget);
            } else {
                $resolvedOrder = $this->clampOrder($requestedOrder, $maxTarget);
                $this->shiftForInsert($conn, $resolvedOrder);
            }

            $id = $this->create([
                'titulo'     => trim((string) ($data['titulo'] ?? '')),
                'subtitle'   => trim((string) ($data['subtitle'] ?? '')),
                'description'=> ($data['description'] ?? '') !== '' ? trim((string) $data['description']) : null,
                'imagen'     => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
                'overlay'    => ($data['overlay'] ?? '') !== '' ? (string) $data['overlay'] : null,
                'orden'      => $resolvedOrder,
                'estado'     => trim((string) ($data['estado'] ?? 'H')),
                'link_btn'   => ($data['link_btn'] ?? '') !== '' ? trim((string) $data['link_btn']) : null,
                'nombre_btn' => ($data['nombre_btn'] ?? '') !== '' ? trim((string) $data['nombre_btn']) : null,
                'created'    => date('Y-m-d H:i:s'),
                'modified'   => null,
            ]);

            $conn->commit();
            return $id;
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function updateRecord(string $id, array $data): bool
    {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();

        try {
            $current = $this->findByIdForUpdate($conn, $id);
            if (!is_array($current)) {
                throw new RuntimeException('Registro slider no encontrado para actualización.');
            }

            $currentOrder = (int) ($current['orden'] ?? 0);
            $requestedOrder = $this->parseOrder($data['orden'] ?? null);
            $maxOrderWithoutCurrent = $this->getMaxOrder($conn, $id);
            $maxTarget = min(self::ORDER_MAX, $maxOrderWithoutCurrent + 1);

            if ($requestedOrder === null) {
                $resolvedOrder = $currentOrder; // Mantiene orden actual si usuario deja vacío.
            } else {
                $resolvedOrder = $this->clampOrder($requestedOrder, $maxTarget);
            }

            if ($resolvedOrder < $currentOrder) {
                $this->shiftForMoveUp($conn, $resolvedOrder, $currentOrder);
            } elseif ($resolvedOrder > $currentOrder) {
                $this->shiftForMoveDown($conn, $currentOrder, $resolvedOrder);
            }

            $updated = $this->update($id, [
                'titulo'     => trim((string) ($data['titulo'] ?? '')),
                'subtitle'   => trim((string) ($data['subtitle'] ?? '')),
                'description'=> ($data['description'] ?? '') !== '' ? trim((string) $data['description']) : null,
                'imagen'     => ($data['imagen'] ?? '') !== '' ? trim((string) $data['imagen']) : null,
                'overlay'    => ($data['overlay'] ?? '') !== '' ? (string) $data['overlay'] : null,
                'orden'      => $resolvedOrder,
                'estado'     => trim((string) ($data['estado'] ?? 'H')),
                'link_btn'   => ($data['link_btn'] ?? '') !== '' ? trim((string) $data['link_btn']) : null,
                'nombre_btn' => ($data['nombre_btn'] ?? '') !== '' ? trim((string) $data['nombre_btn']) : null,
                'modified'   => date('Y-m-d H:i:s'),
            ]);

            $conn->commit();
            return $updated;
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function deleteRecord(string $id): bool
    {
        $conn = $this->db->getConnection();
        $conn->beginTransaction();

        try {
            $current = $this->findByIdForUpdate($conn, $id);
            if (!is_array($current)) {
                $conn->rollBack();
                return false;
            }

            $deleted = $this->delete($id);
            $currentOrder = (int) ($current['orden'] ?? 0);

            $stmt = $conn->prepare("UPDATE {$this->sliderTable}
                                    SET orden = orden - 1
                                    WHERE orden > :orden");
            $stmt->bindValue(':orden', $currentOrder, PDO::PARAM_INT);
            $stmt->execute();

            $conn->commit();
            return $deleted;
        } catch (Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function existsByName(string $value, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->sliderTable,
            'titulo',
            trim($value),
            'id',
            $excludeId
        );
    }

    public function getSuggestedOrder(): int
    {
        $maxOrder = $this->getMaxOrder($this->db->getConnection());
        return max(self::ORDER_MIN, min(self::ORDER_MAX, $maxOrder + 1));
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(titulo LIKE :search OR subtitle LIKE :search2)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }

    private function parseOrder(mixed $value): ?int
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        return (int) $raw;
    }

    private function clampOrder(int $value, int $maxTarget): int
    {
        $upper = max(self::ORDER_MIN, $maxTarget);

        if ($value < self::ORDER_MIN) {
            return self::ORDER_MIN;
        }

        if ($value > $upper) {
            return $upper;
        }

        return $value;
    }

    private function getMaxOrder(PDO $conn, ?string $excludeId = null): int
    {
        $sql = "SELECT MAX(orden) AS max_orden FROM {$this->sliderTable}";
        $params = [];

        if ($excludeId !== null && $excludeId !== '') {
            $sql .= ' WHERE id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $row = $stmt->fetch();
        if (!is_array($row) || $row['max_orden'] === null) {
            return -1;
        }

        return (int) $row['max_orden'];
    }

    private function shiftForInsert(PDO $conn, int $fromOrder): void
    {
        $stmt = $conn->prepare("UPDATE {$this->sliderTable}
                                SET orden = orden + 1
                                WHERE orden >= :from_order
                                ORDER BY orden DESC");
        $stmt->bindValue(':from_order', $fromOrder, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function shiftForMoveUp(PDO $conn, int $newOrder, int $currentOrder): void
    {
        $stmt = $conn->prepare("UPDATE {$this->sliderTable}
                                SET orden = orden + 1
                                WHERE orden >= :new_order
                                  AND orden < :current_order
                                ORDER BY orden DESC");
        $stmt->bindValue(':new_order', $newOrder, PDO::PARAM_INT);
        $stmt->bindValue(':current_order', $currentOrder, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function shiftForMoveDown(PDO $conn, int $currentOrder, int $newOrder): void
    {
        $stmt = $conn->prepare("UPDATE {$this->sliderTable}
                                SET orden = orden - 1
                                WHERE orden > :current_order
                                  AND orden <= :new_order
                                ORDER BY orden ASC");
        $stmt->bindValue(':current_order', $currentOrder, PDO::PARAM_INT);
        $stmt->bindValue(':new_order', $newOrder, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function findByIdForUpdate(PDO $conn, string $id): ?array
    {
        $stmt = $conn->prepare("SELECT id, orden
                                FROM {$this->sliderTable}
                                WHERE id = :id
                                LIMIT 1
                                FOR UPDATE");
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
