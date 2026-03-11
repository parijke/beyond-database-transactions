<?php

declare(strict_types=1);

namespace App;

use PDO;
use Exception;

class StockService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function reduceStock(string $sku): void {
        $stmt = $this->pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE sku = :sku AND quantity > 0");
        $stmt->execute(['sku' => $sku]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Bestand konnte nicht reduziert werden (nicht vorrätig oder SKU unbekannt).");
        }
    }

    public function prepare(string $sku): void {
        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE sku = :sku AND quantity > 0");
        $stmt->execute(['sku' => $sku]);

        if ($stmt->rowCount() === 0) {
            $this->pdo->rollBack();
            throw new Exception("[Stock] Vorbereitung fehlgeschlagen: Kein Bestand für $sku.");
        }
    }

    public function commit(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollback(): void {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
}
