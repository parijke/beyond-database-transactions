<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$pdo = $factory->createMonolithPDO();

$sku = 'console-ltd-123';

echo "Starting Checkout (Monolith)...\n";

try {
    $pdo->beginTransaction();

    // 1. Reduce stock
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE sku = :sku AND quantity > 0");
    $stmt->execute(['sku' => $sku]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Stock empty!");
    }

    // 2. Create order
    $stmt = $pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'CONFIRMED')");
    $stmt->execute(['sku' => $sku]);

    $pdo->commit();
    echo "Success: Order completed and stock reduced.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . " - Rollback performed.\n";
}
