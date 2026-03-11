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

    public function registerMessage(string $orderId): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO inbox (message_id) VALUES (?)");
            $stmt->execute([$orderId]);
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
