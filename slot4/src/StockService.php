<?php

namespace App;

use PDO;

class StockService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function reserve(int $orderId): bool
    {
        // In einer echten App würden wir hier den Bestand prüfen
        // Wir simulieren einen Fehler bei Order ID 999
        if ($orderId === 999) {
            return false;
        }

        $stmt = $this->pdo->prepare('INSERT INTO stock_reservations (order_id, status) VALUES (?, ?)');
        $stmt->execute([$orderId, 'RESERVED']);
        return true;
    }

    public function release(int $orderId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM stock_reservations WHERE order_id = ?');
        $stmt->execute([$orderId]);
    }

    public function isReserved(int $orderId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM stock_reservations WHERE order_id = ?');
        $stmt->execute([$orderId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
