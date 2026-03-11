<?php

declare(strict_types=1);

namespace App;

use PDO;

class PdoOrderService implements OrderService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder(string $sku): void {
        $stmt = $this->pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'CONFIRMED')");
        $stmt->execute(['sku' => $sku]);
    }

    public function prepare(string $sku): bool {
        $this->pdo->beginTransaction();
        $this->createOrder($sku);

        return true;
    }
    public function commit(): void {
        $this->pdo->commit();
    }
}
