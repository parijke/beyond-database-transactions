<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$broker = $factory->createMessageBroker();

echo "Starte Checkout mit 'The Gap' Problem...\n";

try {
    // 1. Bestellung wird committed
    $orderId = $orderService->placeOrder('limited-console-123');
    echo "Bestellung #$orderId erfolgreich angelegt.\n";

    // --- SIMULIERTER ABSTURZ ---
    echo "!!! SIMULIERTER ABSTURZ VOR EVENT-VERSAND !!!\n";
    exit;

    // 2. Event-Versand (wird nie erreicht)
    $broker->publish('OrderPlaced', ['order_id' => $orderId]);

} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
}
