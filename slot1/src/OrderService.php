<?php

declare(strict_types=1);

namespace App;

use PDO;

class OrderService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder(string $sku): void {
        $stmt = $this->pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'CONFIRMED')");
        $stmt->execute(['sku' => $sku]);
    }

    // For participants to implement:
    // public function prepare(string $sku): bool { ... }
    // public function commit(): void { ... }
}
