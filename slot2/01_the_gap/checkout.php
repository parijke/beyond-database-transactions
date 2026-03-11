<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$broker = $factory->createMessageBroker();

echo "Starting Checkout with 'The Gap' problem...\n";

try {
    // 1. Order is committed
    $orderId = $orderService->placeOrder('limited-console-123');
    echo "Order #$orderId successfully created.\n";

    // --- SIMULATED CRASH ---
    echo "!!! SIMULATED CRASH BEFORE EVENT DISPATCH !!!\n";
    exit;

    // 2. Event dispatch (never reached)
    $broker->publish('OrderPlaced', ['order_id' => $orderId]);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
