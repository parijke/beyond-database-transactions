<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$outboxDispatched = $factory->createOutboxRepositoryDispatched();
$outboxDeleting = $factory->createOutboxRepositoryDeleting();

// Datenbank neu initialisieren
echo "Initialisiere Datenbank...\n";
include __DIR__ . '/../setup.php';

echo "Fuege Test-Events hinzu...\n";
$outboxDispatched->addEvent('OrderPlaced', ['id' => 1, 'item' => 'Console']);
$outboxDispatched->addEvent('OrderPlaced', ['id' => 2, 'item' => 'Game']);

echo "Starte relay_dispatched.php...\n";
include __DIR__ . '/relay_dispatched.php';

echo "\nStarte relay_deleting.php...\n";
include __DIR__ . '/relay_deleting.php';

echo "\nPruefe Datenbank-Zustand...\n";
$pdo = $factory->getOrderPdo();
$rows = $pdo->query("SELECT id, dispatched_at FROM outbox")->fetchAll(PDO::FETCH_ASSOC);
echo "Outbox Inhalt:\n";
print_r($rows);
