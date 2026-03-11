<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$stockService = $factory->createStockService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getStockPdo());
$outboxRepo = $factory->createOutboxRepository($factory->getStockPdo());

echo "Stock Worker started (Choreography)...\n";

$broker->subscribe('OrderPlaced', function($event) use ($stockService, $inboxRepo, $outboxRepo, $factory) {
    $orderId = $event['order_id'];
    $eventId = $event['type'] . '_' . $orderId; // Simple ID for idempotency

    $pdo = $factory->getStockPdo();
    $pdo->beginTransaction();

    try {
        if ($inboxRepo->hasBeenProcessed($eventId)) {
            $pdo->rollBack();
            return;
        }

        echo "[Stock] Processing OrderPlaced for #$orderId\n";

        // TASK:
        // 1. Reserve stock via $stockService->reserve($orderId)
        // 2. If successful: Write 'StockReserved' event to the outbox
        // 3. If failed: Write 'StockUnavailable' event to the outbox
        // 4. Mark event as processed in InboxRepo

        /* IMPLEMENT CODE HERE */

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Stock] Fehler: " . $e->getMessage() . "\n";
    }
});
