<?php

namespace App;

use PDO;

class OrderService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createOrder(int $orderId, int $amount): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO orders (id, amount, status) VALUES (?, ?, ?)');
        $stmt->execute([$orderId, $amount, 'PENDING']);
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
    }

    public function getStatus(int $orderId): string
    {
        $stmt = $this->pdo->prepare('SELECT status FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() ?: 'NOT_FOUND';
    }
}
