<?php

namespace App;

use PDO;

class OutboxRepositoryDeleting
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Holt alle Events. Da wir erfolgreich versendete löschen,
     * sind alle vorhandenen Events "pending".
     */
    public function fetchPendingEvents(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM outbox ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Löscht ein Event nach erfolgreichem Versand.
     */
    public function remove(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM outbox WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function addEvent(string $type, array $payload): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO outbox (event_type, payload) VALUES (?, ?)");
        $stmt->execute([$type, json_encode($payload)]);
    }
}
