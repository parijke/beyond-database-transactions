<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$paymentService = $factory->createPaymentService();
$broker = $factory->createMessageBroker();
$inboxRepo = $factory->createInboxRepository($factory->getPaymentPdo());
$outboxRepo = $factory->createOutboxRepository($factory->getPaymentPdo());

echo "Payment Worker started (Choreography)...\n";

$broker->subscribe('StockReserved', function($event) use ($paymentService, $inboxRepo, $outboxRepo, $factory) {
    $orderId = $event['order_id'];
    $amount = $event['amount'] ?? 100;
    $eventId = $event['type'] . '_' . $orderId;

    $pdo = $factory->getPaymentPdo();
    $pdo->beginTransaction();

    try {
        if ($inboxRepo->hasBeenProcessed($eventId)) {
            $pdo->rollBack();
            return;
        }

        echo "[Payment] Processing StockReserved for #$orderId\n";

        // TASK:
        // 1. Execute payment via $paymentService->charge($orderId, $amount)
        // 2. If successful: 'PaymentCompleted' event in outbox
        // 3. If failed: 'PaymentFailed' event in outbox
        // 4. Mark event as processed

        /* IMPLEMENT CODE HERE */

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "[Payment] Fehler: " . $e->getMessage() . "\n";
    }
});
