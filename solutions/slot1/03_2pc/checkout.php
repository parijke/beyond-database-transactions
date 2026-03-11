<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();

$sku = 'console-ltd-123';

echo "Starte 2PC Checkout (Musterlösung)...\n";

try {
    // PHASE 1: Prepare
    echo "[Stock] Preparing...\n";
    $stock->prepare($sku);

    echo "[Order] Preparing...\n";
    $order->prepare($sku);

    // Wenn wir hier ankommen, haben beide Services "OK" gesagt (indem sie keine Exception geworfen haben)
    echo "Beide Services bereit. Starte Phase 2 (Commit)...\n";

    // PHASE 2: Commit
    $stock->commit();
    echo "[Stock] Committed.\n";

    $order->commit();
    echo "[Order] Committed.\n";

    echo "Erfolg: 2PC Checkout abgeschlossen.\n";

} catch (Exception $e) {
    echo "FEHLER im 2PC: " . $e->getMessage() . "\n";
    echo "Führe Rollback für alle Beteiligten durch...\n";
    $stock->rollback();
    $order->rollback();
}
