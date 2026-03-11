<?php

namespace App;

use PDO;
use function json_encode;

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
     * Diese Methode wird von den TN im PdoOrderService genutzt (oder sie bauen das SQL direkt ein)
     */
    public function addEvent(string $type, array $payload): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO outbox (event_type, payload) VALUES (?, ?)");
        $stmt->execute([$type, json_encode($payload)]);
    }

    /**
     * Löscht alle Events aus der Outbox, die bis zur angegebenen ID verarbeitet wurden.
     */
    public function cleanup(int $uptoId): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM outbox WHERE id <= ?");
        $stmt->execute([$uptoId]);
        return $stmt->rowCount();
    }

    public function append(array $event):void {
        $stmt = $this->pdo->prepare("INSERT INTO outbox (payload) VALUES (?)");
        $stmt->execute([json_encode($event)]);
    }
}
