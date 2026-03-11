<?php

declare(strict_types=1);

$inventorySchema = "CREATE TABLE inventory (id INTEGER PRIMARY KEY, sku TEXT NOT NULL, quantity INTEGER NOT NULL);";
$orderSchema = "CREATE TABLE orders (id INTEGER PRIMARY KEY, sku TEXT NOT NULL, status TEXT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP);";
$seedInventory = "INSERT INTO inventory (sku, quantity) VALUES ('console-ltd-123', 10);";

foreach (['monolith.db', 'stock.db', 'order.db'] as $dbFile) {
    if (file_exists(__DIR__ . '/' . $dbFile)) {
        unlink(__DIR__ . '/' . $dbFile);
    }
}

// Monolith
$monolith = new PDO('sqlite:' . __DIR__ . '/monolith.db');
$monolith->exec($inventorySchema);
$monolith->exec($orderSchema);
$monolith->exec($seedInventory);

// Stock
$stock = new PDO('sqlite:' . __DIR__ . '/stock.db');
$stock->exec($inventorySchema);
$stock->exec($seedInventory);

// Order
$order = new PDO('sqlite:' . __DIR__ . '/order.db');
$order->exec($orderSchema);

echo "Datenbanken erfolgreich initialisiert.\n";
