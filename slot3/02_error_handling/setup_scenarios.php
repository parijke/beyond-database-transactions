<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

// Reset database (only if necessary, here already done by setup.php)
// require __DIR__ . '/../setup.php';

echo "Creating test scenarios for error handling...\n";

// 1. A normal message
$inboxRepo->registerMessage('msg_ok_1', 'order_1', 'OrderPlaced', ['order_id' => 1]);

// 2. A message that will provoke an error (aggregate order_2)
$inboxRepo->registerMessage('msg_fail_1', 'order_2', 'OrderPlaced', ['order_id' => 2, 'cause_error' => true]);

// 3. A follow-up message for order_2 (should be blocked)
$inboxRepo->registerMessage('msg_fail_2', 'order_2', 'PaymentReceived', ['order_id' => 2]);

// 4. A message for another aggregate (order_3) - should pass through
$inboxRepo->registerMessage('msg_ok_2', 'order_3', 'OrderPlaced', ['order_id' => 3]);

echo "Scenarios created. Use 'php status.php' to check.\n";
