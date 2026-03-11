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
     * Versucht eine Message ID in die Inbox zu schreiben.
     * Gibt true zurück, wenn es erfolgreich war (Nachricht neu).
     * Gibt false zurück, wenn die ID bereits existiert (Duplikat).
     */
    public function registerMessage(string $messageId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO inbox (message_id) VALUES (?)");
            $stmt->execute([$messageId]);
            return true;
        } catch (PDOException $e) {
            // Check if it's a unique constraint violation (SQLITE_CONSTRAINT)
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }
}
