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
            // 1. Bestellung anlegen
            $stmt = $this->pdo->prepare("INSERT INTO orders (item_id, status) VALUES (?, 'CONFIRMED')");
            $stmt->execute([$itemId]);
            $orderId = (int)$this->pdo->lastInsertId();

            // 2. Outbox-Eintrag schreiben (Atomar in der gleichen Transaktion!)
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
