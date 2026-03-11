<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$outboxRepo = $factory->createOutboxRepository();
$checkpointRepo = $factory->createCheckpointRepository();
$broker = $factory->createMessageBroker();

echo "Relay-Worker starting...\n";

// 1. Load last checkpoint
$lastId = $checkpointRepo->getLastProcessedId('relay_worker');
echo "Starting at outbox ID: $lastId\n";

$shouldCrash = in_array('--crash', $argv);

// 2. Load new events
$events = $outboxRepo->fetchPendingEvents($lastId);

if (empty($events)) {
    echo "No new events to process.\n";
    exit;
}

foreach ($events as $event) {
    echo "Processing event #{$event['id']} ({$event['event_type']})...\n";

    $broker->publish($event['event_type'], json_decode($event['payload'], true));

    if ($shouldCrash) {
        echo "-- CRASH --- " . PHP_EOL;
        exit(1);
    }

    $checkpointRepo->updateCheckpoint('relay_worker', $event['id']);
    echo "Checkpoint updated to id {$event['id']}.\n";

}
