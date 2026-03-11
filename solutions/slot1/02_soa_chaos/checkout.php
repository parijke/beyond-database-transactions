<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();

$sku = 'console-ltd-123';

echo "Starte naiven SOA-Checkout...\n";

try {
    // 1. Im Lager reservieren
    $stock->reduceStock($sku);
    echo "[Stock] Reservierung erfolgreich.\n";

    // --- HIER: Simuliere einen Fehler (z.B. throw new Exception() oder exit) ---
    // throw new Exception("Netzwerkfehler beim Aufruf des PdoOrderService!");

    // 2. Bestellung anlegen
    $order->createOrder($sku);
    echo "[Order] Bestellung erfolgreich angelegt.\n";

    echo "Erfolg: Checkout abgeschlossen.\n";

} catch (Exception $e) {
    echo "FEHLER im Checkout: " . $e->getMessage() . "\n";
}
