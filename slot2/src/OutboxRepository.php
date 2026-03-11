<?php

namespace App;

use PDO;

class OutboxRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchPendingEvents(int $lastId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM outbox WHERE id > ? ORDER BY id ASC");
        $stmt->execute([$lastId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * This method is used by participants in the PdoOrderService (or they build the SQL directly)
     */
    public function addEvent(string $type, array $payload): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO outbox (event_type, payload) VALUES (?, ?)");
        $stmt->execute([$type, json_encode($payload)]);
    }
}
