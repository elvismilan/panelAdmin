<?php

namespace App\Models;

use PanelAdmin\Core\Model;

class ContactoModel extends Model
{
    private string $contactoTable;
    private string $lastActionLog = '';

    public function __construct()
    {
        parent::__construct();
        $this->contactoTable = $this->tableName('contactos');
        $this->setTable('contactos');
        $this->setPrimaryKey('id');
    }

    public function createFromPublic(array $data): string
    {
        $nombre = trim((string) ($data['nombre'] ?? ''));

        $id = $this->create([
            'nombre' => $nombre,
            'telefono' => trim((string) ($data['telefono'] ?? '')) !== '' ? trim((string) $data['telefono']) : null,
            'email' => trim((string) ($data['email'] ?? '')),
            'mensaje' => trim((string) ($data['mensaje'] ?? '')),
            'ip' => ($data['ip'] ?? '') !== '' ? (string) $data['ip'] : null,
            'user_agent' => ($data['user_agent'] ?? '') !== '' ? mb_substr((string) $data['user_agent'], 0, 255) : null,
            'created' => date('Y-m-d H:i:s'),
        ]);

        $this->lastActionLog = "Mensaje de contacto recibido: {$nombre} (ID {$id})";

        return $id;
    }

    public function countRecentByIp(string $ip, int $minutes = 30): int
    {
        if (trim($ip) === '') {
            return 0;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM {$this->contactoTable}
                WHERE ip = :ip AND created >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)";

        return $this->countByQuery($sql, ['ip' => $ip, 'minutos' => $minutes]);
    }

    public function getLastActionLog(): string
    {
        return $this->lastActionLog !== '' ? $this->lastActionLog : parent::getLastActionLog();
    }
}
