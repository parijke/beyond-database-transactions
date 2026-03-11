<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

// Simulate incoming message
$incomingMessage = [
    'id' => 'msg_12345', // Unique ID
    'type' => 'OrderPlaced',
    'payload' => ['order_id' => 42, 'item_id' => 'keyboard']
];

echo "Receiving message #{$incomingMessage['id']}...\n";

// TASK: Use the InboxRepository to ensure that this message
// is processed only once.

$isNew = false; // Add logic here!

if ($isNew) {
    echo "Processing message: " . json_encode($incomingMessage['payload']) . "\n";
    echo "Message processed successfully.\n";
} else {
    echo "Ignoring duplicate: Message #{$incomingMessage['id']} has already been processed.\n";
}
