<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getOrderPdo());

echo "Order Worker started (Choreography)...\n";

$handler = function($event) use ($orderService, $inboxRepo, $factory) {
    $orderId = $event['order_id'];
    $eventId = $event['type'] . '_' . $orderId;

    $pdo = $factory->getOrderPdo();
    $pdo->beginTransaction();

    try {
        if ($inboxRepo->hasBeenProcessed($eventId)) {
            $pdo->rollBack();
            return;
        }

        echo "[Order] Processing {$event['type']} for #$orderId\n";

        // TASK:
        // Update the status of the order based on the event type:
        // - 'PaymentCompleted' -> 'SUCCESS'
        // - 'PaymentFailed'    -> 'PAYMENT_FAILED'
        // - 'StockUnavailable' -> 'OUT_OF_STOCK'

        /* IMPLEMENT CODE HERE */

        $inboxRepo->markAsProcessed($eventId, json_encode($event));
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Order] Fehler: " . $e->getMessage() . "\n";
    }
};

$broker->subscribe('PaymentCompleted', $handler);
$broker->subscribe('PaymentFailed', $handler);
$broker->subscribe('StockUnavailable', $handler);
