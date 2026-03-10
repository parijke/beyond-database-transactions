<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();

$sku = 'console-ltd-123';

echo "Starting 2PC Checkout...\n";

/**
 * TASK:
 * 1. Implement a prepare() and commit() method in both StockService and OrderService.
 * 2. Use these methods here to build a secure checkout.
 */

try {
    // PHASE 1: Prepare
    // $stock->prepare($sku);
    // $order->prepare($sku);

    // PHASE 2: Commit
    // $stock->commit();
    // $order->commit();

    echo "Success: 2PC Checkout completed.\n";

} catch (Exception $e) {
    // If one fails: Rollback (if possible)
    echo "ERROR in 2PC: " . $e->getMessage() . "\n";
}
