<?php

namespace App;

use PDO;
use Exception;
use function json_encode;

class OrderService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * In this version we only write the order.
     * In the exercise, the participants must extend this with outbox writing.
     */
    public function placeOrder(string $itemId): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO orders (item_id, status) VALUES (?, 'CONFIRMED')");
            $stmt->execute([$itemId]);
            $orderId = (int)$this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("INSERT INTO outbox (event_type, payload) VALUES (?, ?)");
            $payload = json_encode(['order_id' => $orderId, 'item_id' => $itemId]);
            $stmt->execute(['OrderPlaced', $payload]);

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
