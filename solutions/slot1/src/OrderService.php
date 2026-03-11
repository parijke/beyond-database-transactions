<?php

declare(strict_types=1);

namespace App;

use PDO;
use Exception;

class OrderService {
    private PDO $pdo;
    private ?int $lastInsertedOrderId = null;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder(string $sku): void {
        $stmt = $this->pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'CONFIRMED')");
        $stmt->execute(['sku' => $sku]);
    }

    public function prepare(string $sku): void {
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'PENDING_2PC')");
        $stmt->execute(['sku' => $sku]);
        $this->lastInsertedOrderId = (int)$this->pdo->lastInsertId();
    }

    public function commit(): void {
        if ($this->pdo->inTransaction() && $this->lastInsertedOrderId !== null) {
            $stmt = $this->pdo->prepare("UPDATE orders SET status = 'CONFIRMED' WHERE id = :id");
            $stmt->execute(['id' => $this->lastInsertedOrderId]);
            $this->pdo->commit();
            $this->lastInsertedOrderId = null;
        }
    }

    public function rollback(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
            $this->lastInsertedOrderId = null;
        }
    }
}
