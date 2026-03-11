<?php

namespace App;

use PDO;

class CheckpointRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getLastProcessedId(string $name): int
    {
        $stmt = $this->pdo->prepare("SELECT last_processed_id FROM checkpoints WHERE name = ?");
        $stmt->execute([$name]);
        return (int)$stmt->fetchColumn();
    }

    public function updateCheckpoint(string $name, int $lastId): void
    {
        $stmt = $this->pdo->prepare("UPDATE checkpoints SET last_processed_id = ? WHERE name = ?");
        $stmt->execute([$lastId, $name]);
    }

    /**
     * Ermittelt die ID, bis zu der ALLE Worker verarbeitet haben.
     * Dies ist das Minimum aller Checkpoints.
     */
    public function getMinimumCheckpoint(): int
    {
        $stmt = $this->pdo->query("SELECT MIN(last_processed_id) FROM checkpoints");
        return (int)$stmt->fetchColumn();
    }
}
