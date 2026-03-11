<?php

$orderDb = __DIR__ . '/order.db';
$stockDb = __DIR__ . '/stock.db';
$paymentDb = __DIR__ . '/payment.db';

foreach ([$orderDb, $stockDb, $paymentDb] as $db) {
    if (file_exists($db)) {
        unlink($db);
    }
}

$orderPdo = new PDO('sqlite:' . $orderDb);
$orderPdo->exec('CREATE TABLE orders (id INTEGER PRIMARY KEY, amount INTEGER, status TEXT)');
$orderPdo->exec('CREATE TABLE outbox (id INTEGER PRIMARY KEY AUTOINCREMENT, payload TEXT)');
$orderPdo->exec('CREATE TABLE inbox (id TEXT PRIMARY KEY, payload TEXT, processed_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
$orderPdo->exec('CREATE TABLE checkpoints (name TEXT PRIMARY KEY, last_processed_id INTEGER)');

$stockPdo = new PDO('sqlite:' . $stockDb);
$stockPdo->exec('CREATE TABLE stock_reservations (order_id INTEGER PRIMARY KEY, status TEXT)');
$stockPdo->exec('CREATE TABLE outbox (id INTEGER PRIMARY KEY AUTOINCREMENT, payload TEXT)');
$stockPdo->exec('CREATE TABLE inbox (id TEXT PRIMARY KEY, payload TEXT, processed_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
$stockPdo->exec('CREATE TABLE checkpoints (name TEXT PRIMARY KEY, last_processed_id INTEGER)');

$paymentPdo = new PDO('sqlite:' . $paymentDb);
$paymentPdo->exec('CREATE TABLE payments (order_id INTEGER PRIMARY KEY, amount INTEGER, status TEXT)');
$paymentPdo->exec('CREATE TABLE outbox (id INTEGER PRIMARY KEY AUTOINCREMENT, payload TEXT)');
$paymentPdo->exec('CREATE TABLE inbox (id TEXT PRIMARY KEY, payload TEXT, processed_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
$paymentPdo->exec('CREATE TABLE checkpoints (name TEXT PRIMARY KEY, last_processed_id INTEGER)');

echo "Databases for Slot 4 successfully created.\n";
