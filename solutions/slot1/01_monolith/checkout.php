<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$pdo = $factory->createMonolithPDO();

$sku = 'console-ltd-123';

echo "Starte Checkout (Monolith)...\n";

try {
    $pdo->beginTransaction();

    // 1. Bestand reduzieren
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE sku = :sku AND quantity > 0");
    $stmt->execute(['sku' => $sku]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Lager leer!");
    }

    // 2. Bestellung anlegen
    $stmt = $pdo->prepare("INSERT INTO orders (sku, status) VALUES (:sku, 'CONFIRMED')");
    $stmt->execute(['sku' => $sku]);

    $pdo->commit();
    echo "Erfolg: Bestellung abgeschlossen und Bestand reduziert.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Fehler: " . $e->getMessage() . " - Rollback durchgeführt.\n";
}
