<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();
$email = $factory->createEmailService();

$sku = 'console-ltd-123';

echo "Starte Checkout mit E-Mail Nebenwirkung (Musterlösung)...\n";

try {
    // 1. Lokaler Teil via 2PC vorbereiten
    $stock->prepare($sku);
    $order->prepare($sku);

    // 2. Die externe Nebenwirkung (E-Mail)
    // Wir schicken sie hier raus, "weil wir ja schon vorbereitet sind"
    $email->sendConfirmation('kunde@test.de', $sku);

    // 3. Simuliere einen Fehler GENAU HIER (z.B. Koordinator stürzt ab)
    echo "!!! SIMULIERTER CRASH VOR DEN COMMITS !!!\n";
    throw new Exception("Crash des Koordinators nach E-Mail-Versand, aber vor DB-Commits.");

    $stock->commit();
    $order->commit();

    echo "Erfolg: Checkout inkl. E-Mail abgeschlossen.\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";

    // Lokaler Rollback
    $stock->rollback();
    $order->rollback();

    echo "\nZUSTANDSANALYSE:\n";
    echo "- Die E-Mail wurde versendet (siehe oben).\n";
    echo "- Die Datenbanken wurden zurückgerollt (Bestand ist noch da, Order fehlt).\n";
    echo "=> Das ist die Inkonsistenz, die 2PC nicht lösen kann!\n";
}
