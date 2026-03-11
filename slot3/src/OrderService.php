<?php

namespace App;

use PDO;
use Exception;

class OrderService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function placeOrder(string $itemId): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO orders (item_id, status) VALUES (?, 'CONFIRMED')");
            $stmt->execute([$itemId]);
            $orderId = (int)$this->pdo->lastInsertId();

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
