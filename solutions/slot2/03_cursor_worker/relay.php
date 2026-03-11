<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$outboxRepo = $factory->createOutboxRepository();
$checkpointRepo = $factory->createCheckpointRepository();
$broker = $factory->createMessageBroker();

$shouldCrash = in_array('--crash', $argv);

echo "Relay-Worker (Solution) startet...\n";

// 1. Letzten Checkpoint laden
$lastId = $checkpointRepo->getLastProcessedId('relay_worker');
echo "Starte bei Outbox-ID: $lastId\n";

// 2. Neue Events laden
$events = $outboxRepo->fetchPendingEvents($lastId);

if (empty($events)) {
    echo "Keine neuen Events zum Verarbeiten.\n";
    exit;
}

foreach ($events as $event) {
    echo "Verarbeite Event #{$event['id']} ({$event['event_type']})...\n";

    // a) Event an den Broker senden
    $broker->publish($event['event_type'], json_decode($event['payload'], true));

    // Experiment: Simulierte Absturzstelle
    if ($shouldCrash) {
        echo "!!! SIMULIERTER ABSTURZ NACH SENDEN, VOR CHECKPOINT-UPDATE !!!\n";
        exit(1);
    }

    // b) Checkpoint aktualisieren
    $checkpointRepo->updateCheckpoint('relay_worker', $event['id']);
    echo "Checkpoint auf ID {$event['id']} gesetzt.\n";
}

echo "Alle Events erfolgreich verarbeitet.\n";
