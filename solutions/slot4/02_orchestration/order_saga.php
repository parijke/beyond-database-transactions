<?php

require_once __DIR__ . '/../../../slot4/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$stockService = $factory->createStockService();
$paymentService = $factory->createPaymentService();

$orderId = (int)($argv[1] ?? 1);
$amount = (int)($argv[2] ?? 100);

echo "Saga Orchestrator gestartet für Order #$orderId (Lösung)...\n";

try {
    // Schritt 1: Order erstellen
    echo "Schritt 1: Order erstellen...\n";
    $orderService->createOrder($orderId, $amount);

    // Schritt 2: Bestand reservieren
    echo "Schritt 2: Bestand reservieren...\n";
    $success = $stockService->reserve($orderId);

    if (!$success) {
        echo "FEHLER: Bestand nicht verfügbar.\n";
        $orderService->updateStatus($orderId, 'OUT_OF_STOCK');
        exit;
    }

    // Schritt 3: Zahlung ausführen
    echo "Schritt 3: Zahlung ausführen...\n";
    $paid = $paymentService->charge($orderId, $amount);

    if (!$paid) {
        echo "FEHLER: Zahlung fehlgeschlagen. Starte Kompensation...\n";

        // Kompensation: Bestand wieder freigeben
        $stockService->release($orderId);
        echo "[Kompensation] Bestand für #$orderId wieder freigegeben.\n";

        $orderService->updateStatus($orderId, 'PAYMENT_FAILED');
        exit;
    }

    // Schritt 4: Erfolg
    echo "Schritt 4: Finalisierung...\n";
    $orderService->updateStatus($orderId, 'SUCCESS');
    echo "Bestellung #$orderId erfolgreich abgeschlossen!\n";

} catch (Exception $e) {
    echo "Kritischer Fehler in der Saga: " . $e->getMessage() . "\n";
}
