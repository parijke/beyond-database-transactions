<?php

$orderDbFile = __DIR__ . '/order.db';
if (file_exists($orderDbFile)) {
    unlink($orderDbFile);
}

$orderPdo = new PDO('sqlite:' . $orderDbFile);
$orderPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Orders Table
$orderPdo->exec("CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id TEXT NOT NULL,
    status TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Outbox Table
$orderPdo->exec("CREATE TABLE outbox (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type TEXT NOT NULL,
    payload TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Checkpoints Table
$orderPdo->exec("CREATE TABLE checkpoints (
    name TEXT PRIMARY KEY,
    last_processed_id INTEGER NOT NULL DEFAULT 0
)");

// Inbox Table (For Idempotency & Error Handling)
$orderPdo->exec("CREATE TABLE inbox (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    message_id TEXT UNIQUE NOT NULL,
    aggregate_id TEXT NOT NULL,
    event_type TEXT NOT NULL,
    payload TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'PENDING',
    attempts INTEGER NOT NULL DEFAULT 0,
    last_error TEXT,
    processed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Initialize Checkpoint for the relay worker
$orderPdo->exec("INSERT INTO checkpoints (name, last_processed_id) VALUES ('relay_worker', 0)");

echo "Slot 3 Database initialized successfully.\n";
