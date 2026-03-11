<?php

require_once __DIR__ . '/../../../slot4/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$stockService = $factory->createStockService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getStockPdo());
$outboxRepo = $factory->createOutboxRepository($factory->getStockPdo());

echo "Stock Worker gestartet (Choreographie - Lösung)...\n";

$broker->subscribe('OrderPlaced', function($event) use ($stockService, $inboxRepo, $outboxRepo, $factory) {
    $orderId = $event['order_id'];
    $eventId = $event['type'] . '_' . $orderId;

    $pdo = $factory->getStockPdo();
    $pdo->beginTransaction();

    try {
        if ($inboxRepo->hasBeenProcessed($eventId)) {
            $pdo->rollBack();
            return;
        }

        echo "[Stock] Verarbeite OrderPlaced für #$orderId\n";

        $success = $stockService->reserve($orderId);

        if ($success) {
            $outboxRepo->append([
                'type' => 'StockReserved',
                'order_id' => $orderId,
                'amount' => $event['amount']
            ]);
        } else {
            $outboxRepo->append([
                'type' => 'StockUnavailable',
                'order_id' => $orderId
            ]);
        }

        $inboxRepo->markAsProcessed($eventId, json_encode($event));
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Stock] Fehler: " . $e->getMessage() . "\n";
    }
});
