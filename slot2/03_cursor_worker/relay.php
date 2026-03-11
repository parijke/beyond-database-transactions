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

// 2. Load new events
$events = $outboxRepo->fetchPendingEvents($lastId);

if (empty($events)) {
    echo "No new events to process.\n";
    exit;
}

foreach ($events as $event) {
    echo "Processing event #{$event['id']} ({$event['event_type']})...\n";

    // Task:
    // a) Send event to the broker
    // b) Update checkpoint
    // c) Experiment: Insert an 'exit;' between sending and checkpoint update!

    echo "!!! IMPLEMENT HERE !!!\n";
}
