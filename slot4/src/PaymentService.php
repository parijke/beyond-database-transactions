<?php

namespace App;

use PDO;

class PaymentService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function charge(int $orderId, int $amount): bool
    {
        // Wir simulieren einen Zahlungsfehler bei Beträgen > 1000
        if ($amount > 1000) {
            return false;
        }

        $stmt = $this->pdo->prepare('INSERT INTO payments (order_id, amount, status) VALUES (?, ?, ?)');
        $stmt->execute([$orderId, $amount, 'SUCCESS']);
        return true;
    }

    public function refund(int $orderId): void
    {
        $stmt = $this->pdo->prepare('UPDATE payments SET status = ? WHERE order_id = ?');
        $stmt->execute(['REFUNDED', $orderId]);
    }

    public function isPaid(int $orderId): bool
    {
        $stmt = $this->pdo->prepare('SELECT status FROM payments WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetchColumn() === 'SUCCESS';
    }
}
