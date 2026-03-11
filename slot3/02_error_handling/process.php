<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

echo "Starting Inbox Worker...\n";

while ($message = $inboxRepo->fetchNextPending()) {
    echo "Processing message #{$message['id']} (Type: {$message['event_type']}, Aggregate: {$message['aggregate_id']})...\n";

    $payload = json_encode($message['payload']); // Payload is already JSON in repository-fetch (depending on implementation)
    // But we stored it as JSON in the InboxRepository. PDO fetch returns it as a string.
    $data = json_decode($message['payload'], true);

    // TASK 2: Aggregate Blocking
    // Check if there is already a failed message for this aggregate.
    // If yes: Mark this message as 'BLOCKED' and continue with the next one.

    if ($inboxRepo->hasFailedPredecessor($message['aggregate_id'], $message['id'])) {
        echo "  [BLOCK] Aggregate {$message['aggregate_id']} is blocked. Parking message.\n";
        $inboxRepo->markAsBlocked((int)$message['id']);
        continue;
    }

    try {
        // Simulate business logic
        if (isset($data['cause_error']) && $data['cause_error'] === true) {
            throw new Exception("Simulated severe error during processing!");
        }

        echo "  [OK] Message successfully processed.\n";

        // TASK 1: Mark as successfully processed
        $inboxRepo->markAsProcessed($message['id']);

    } catch (Exception $e) {
        echo "  [ERROR] Error: " . $e->getMessage() . "\n";

        // TASK 1: Mark as failed
        $inboxRepo->markAsFailed($message['id'], $e->getMessage());
    }
}

echo "No more messages.\n";
