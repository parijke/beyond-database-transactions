<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();

echo "Task: Extend App\OrderService::placeOrder() so that an outbox entry is also written.\n";

try {
    $orderId = $orderService->placeOrder('limited-console-456');
    echo "Order #$orderId created.\n";
    echo "Now check with 'php status.php' if there is also an entry in the 'outbox' table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
