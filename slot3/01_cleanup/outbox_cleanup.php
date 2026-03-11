<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$checkpointRepo = $factory->createCheckpointRepository();
$outboxRepo = $factory->createOutboxRepository();

// Outbox cleanup may only occur up to the smallest checkpoint of ALL consumers.
// If there is only one consumer (relay_worker), it's simple.
// If there are multiple, all must be considered.

$minCheckpoint = $checkpointRepo->getMinimumCheckpoint();

echo "Smallest common checkpoint: $minCheckpoint\n";

if ($minCheckpoint > 0) {
    $deletedCount = $outboxRepo->cleanup($minCheckpoint);
    echo "Outbox Cleanup: $deletedCount entries up to ID $minCheckpoint deleted.\n";
} else {
    echo "No outbox cleanup possible (minimum checkpoint is 0).\n";
}
