<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$outboxRepo = $factory->createOutboxRepository($factory->getOrderPdo());

$orderId = (int)($argv[1] ?? 1);
$amount = (int)($argv[2] ?? 100);

// Task: Create the order and write an 'OrderPlaced' event to the outbox.
// Use a transaction!

$pdo = $factory->getOrderPdo();
$pdo->beginTransaction();

try {
    $orderService->createOrder($orderId, $amount);

    $event = [
        'type' => 'OrderPlaced',
        'order_id' => $orderId,
        'amount' => $amount
    ];

    $outboxRepo->append($event);

    $pdo->commit();
    echo "Order #$orderId for $amount € placed (Status: PENDING).\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
