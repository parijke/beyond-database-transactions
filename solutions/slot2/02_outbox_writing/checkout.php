<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();

echo "Aufgabe: Erweitere App\OrderService::placeOrder() so, dass auch ein Outbox-Eintrag geschrieben wird.\n";

try {
    $orderId = $orderService->placeOrder('limited-console-456');
    echo "Bestellung #$orderId angelegt.\n";
    echo "Pruefe jetzt mit 'php status.php', ob auch ein Eintrag in der 'outbox' Tabelle vorhanden ist.\n";
} catch (Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
}
