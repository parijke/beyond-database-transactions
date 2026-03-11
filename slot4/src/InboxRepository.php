<?php

namespace App;

use PDO;
use PDOException;

class InboxRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Versucht eine Message in die Inbox zu schreiben.
     * Gibt true zurück, wenn es erfolgreich war (Nachricht neu).
     * Gibt false zurück, wenn die message_id bereits existiert (Duplikat).
     */
    public function registerMessage(string $messageId, string $aggregateId, string $eventType, array $payload): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO inbox (message_id, aggregate_id, event_type, payload, status) VALUES (?, ?, ?, ?, 'PENDING')");
            $stmt->execute([
                $messageId,
                $aggregateId,
                $eventType,
                json_encode($payload)
            ]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }

    public function fetchNextPending(): ?array
    {
        $stmt = $this->pdo->query("SELECT * FROM inbox WHERE status = 'PENDING' ORDER BY id ASC LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function fetchNextUnblockedPending(): ?array
    {
        $stmt = $this->pdo->query("
            SELECT i.* 
            FROM inbox i 
            WHERE i.status = 'PENDING' 
              AND NOT EXISTS (
                  SELECT 1 
                  FROM inbox i2 
                  WHERE i2.aggregate_id = i.aggregate_id 
                    AND (i2.status = 'FAILED' OR i2.status = 'BLOCKED')
              )
            ORDER BY i.id ASC 
            LIMIT 1
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function hasFailedPredecessor(string $aggregateId, int $currentId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM inbox WHERE aggregate_id = ? AND id < ? AND status = 'FAILED' LIMIT 1");
        $stmt->execute([$aggregateId, $currentId]);
        return (bool)$stmt->fetchColumn();
    }

    public function markAsProcessed(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE inbox SET status = 'SUCCESS', processed_at = datetime('now') WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function markAsFailed(int $id, string $error): void
    {
        $stmt = $this->pdo->prepare("UPDATE inbox SET status = 'FAILED', attempts = attempts + 1, last_error = ? WHERE id = ?");
        $stmt->execute([$error, $id]);
    }

    public function markAsBlocked(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE inbox SET status = 'BLOCKED' WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Löscht alte Einträge aus der Inbox, die vor einer bestimmten Zeit verarbeitet wurden.
     * $olderThanInterval ist ein SQL-Intervall-String, z.B. '-7 days'
     */
    public function cleanup(string $olderThanInterval = '-7 days'): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM inbox WHERE processed_at < datetime('now', ?)");
        $stmt->execute([$olderThanInterval]);
        return $stmt->rowCount();
    }
}
