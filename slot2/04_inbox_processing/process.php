<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

$incomingMessage = [
    'id' => 'msg_12345',
    'type' => 'OrderPlaced',
    'payload' => ['order_id' => 42, 'item_id' => 'keyboard']
];

echo "Receiving message #{$incomingMessage['id']}...\n";

$isNew = $inboxRepo->registerMessage($incomingMessage['id']);

if ($isNew) {
    echo "Working on message: " . json_encode($incomingMessage['payload']) . "\n";
    echo "done.\n";
} else {
    echo "Ignoring duplicate: message #{$incomingMessage['id']} already done.\n";
}
