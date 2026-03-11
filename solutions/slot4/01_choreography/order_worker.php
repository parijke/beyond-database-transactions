<?php

require_once __DIR__ . '/../../../slot4/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getOrderPdo());

echo "Order Worker gestartet (Choreographie - Lösung)...\n";

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

        echo "[Order] Verarbeite {$event['type']} für #$orderId\n";

        if ($event['type'] === 'PaymentCompleted') {
            $orderService->updateStatus($orderId, 'SUCCESS');
        } elseif ($event['type'] === 'PaymentFailed') {
            $orderService->updateStatus($orderId, 'PAYMENT_FAILED');
        } elseif ($event['type'] === 'StockUnavailable') {
            $orderService->updateStatus($orderId, 'OUT_OF_STOCK');
        }

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
