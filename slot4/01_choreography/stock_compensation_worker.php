<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$stockService = $factory->createStockService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getStockPdo());

echo "Stock Compensation Worker started (Choreography)...\n";

$broker->subscribe('PaymentFailed', function($event) use ($stockService, $inboxRepo, $factory) {
    $orderId = $event['order_id'];
    $eventId = 'Compensate_' . $event['type'] . '_' . $orderId;

    $pdo = $factory->getStockPdo();
    $pdo->beginTransaction();

    try {
        if ($inboxRepo->hasBeenProcessed($eventId)) {
            $pdo->rollBack();
            return;
        }

        echo "[Stock] Compensation: Releasing stock for #$orderId\n";

        // TASK: Release stock via $stockService->release($orderId)
        $stockService->release($orderId);

        $inboxRepo->markAsProcessed($eventId, json_encode($event));
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Stock] Fehler bei Kompensation: " . $e->getMessage() . "\n";
    }
});
