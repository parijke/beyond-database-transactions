<?php

require_once __DIR__ . '/../../../slot4/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$stockService = $factory->createStockService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getStockPdo());

echo "Stock Compensation Worker gestartet (Choreographie - Lösung)...\n";

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

        echo "[Stock] Kompensation: Gebe Bestand für #$orderId frei\n";

        $stockService->release($orderId);

        $inboxRepo->markAsProcessed($eventId, json_encode($event));
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Stock] Fehler bei Kompensation: " . $e->getMessage() . "\n";
    }
});
