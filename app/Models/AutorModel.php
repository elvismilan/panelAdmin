<?php

namespace App\Models;

use PanelAdmin\Core\Model;

/**
 * Usuarios activos para elegir el autor de un articulo (solo lectura).
 */
class AutorModel extends Model
{
    /**
     * @return array<int, array{usu_id: string, nombre: string}>
     */
    public function getActiveForSelect(): array
    {
        $usuario = $this->tableName('usuario');
        $persona = $this->tableName('persona');

        $sql = "SELECT u.usu_id,
                       TRIM(CONCAT(COALESCE(p.per_nombre, ''), ' ', COALESCE(p.per_apellido, ''))) AS nombre
                FROM {$usuario} u
                LEFT JOIN {$persona} p ON p.per_id = u.usu_per_id
                WHERE u.usu_estado = :estado
                ORDER BY nombre ASC, u.usu_id ASC";

        return $this->db->query($sql, ['estado' => (string) ($_ENV['AUTH_ACTIVE_STATUS'] ?? 'H')])->fetchAll();
    }
}
