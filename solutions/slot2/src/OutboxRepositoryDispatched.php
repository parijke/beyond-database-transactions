<?php

namespace App;

use PDO;

class OutboxRepositoryDispatched
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Holt alle Events, die noch nicht versendet wurden.
     */
    public function fetchPendingEvents(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM outbox WHERE dispatched_at IS NULL ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Markiert ein Event als versendet.
     */
    public function markAsDispatched(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE outbox SET dispatched_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function addEvent(string $type, array $payload): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO outbox (event_type, payload) VALUES (?, ?)");
        $stmt->execute([$type, json_encode($payload)]);
    }
}
