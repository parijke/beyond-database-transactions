<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$pdo = $factory->getOrderPdo();
$outboxRepo = $factory->createOutboxRepository();
$checkpointRepo = $factory->createCheckpointRepository();
$inboxRepo = $factory->createInboxRepository();

echo "Erzeuge Testdaten...\n";

// 1. Outbox Daten
$outboxRepo->addEvent('order.created', ['id' => 1]);
$outboxRepo->addEvent('order.created', ['id' => 2]);
$outboxRepo->addEvent('order.created', ['id' => 3]);

// 2. Checkpoint setzen (Worker hat bis ID 2 verarbeitet)
$checkpointRepo->updateCheckpoint('relay_worker', 2);

// 3. Inbox Daten (einige alte, einige neue)
$pdo->exec("INSERT INTO inbox (message_id, processed_at) VALUES ('msg-old-1', datetime('now', '-10 days'))");
$pdo->exec("INSERT INTO inbox (message_id, processed_at) VALUES ('msg-old-2', datetime('now', '-2 days'))");
$pdo->exec("INSERT INTO inbox (message_id, processed_at) VALUES ('msg-new-1', datetime('now'))");

echo "Testdaten bereit.\n";
