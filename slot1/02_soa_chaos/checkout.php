<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();

$sku = 'console-ltd-123';

echo "Starting naive SOA checkout...\n";

try {
    // 1. Reserve in stock
    $stock->reduceStock($sku);
    echo "[Stock] Reservation successful.\n";

    // --- HERE: Simulate an error (e.g. throw new Exception() or exit) ---
    // throw new Exception("Network error while calling OrderService!");

    // 2. Create order
    $order->createOrder($sku);
    echo "[Order] Order successfully created.\n";

    echo "Success: Checkout completed.\n";

} catch (Exception $e) {
    echo "ERROR in checkout: " . $e->getMessage() . "\n";
}
