<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$broker = $factory->createMessageBroker();

$serviceName = $argv[1] ?? 'order';

switch ($serviceName) {
    case 'order':
        $pdo = $factory->getOrderPdo();
        break;
    case 'stock':
        $pdo = $factory->getStockPdo();
        break;
    case 'payment':
        $pdo = $factory->getPaymentPdo();
        break;
    default:
        die("Unknown service: $serviceName\n");
}

$outboxRepo = $factory->createOutboxRepository($pdo);
$checkpointRepo = $factory->createCheckpointRepository($pdo);

echo "Relay for $serviceName started...\n";

while (true) {
    $lastId = $checkpointRepo->getLastProcessedId("relay_$serviceName");
    $messages = $outboxRepo->fetchPendingEvents($lastId);

    foreach ($messages as $msg) {
        $event = json_decode($msg['payload'], true);
        echo "[$serviceName] Publishing event: {$event['type']} for Order #{$event['order_id']}\n";

        $broker->publish($event['type'], $event);
        $checkpointRepo->updateCheckpoint("relay_$serviceName", $msg['id']);
    }

    usleep(500000);
}
