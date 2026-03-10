<?php

declare(strict_types=1);

use App\ServiceFactory;

require __DIR__ . '/../src/autoload.php';

$factory = new ServiceFactory();
$stock = $factory->createStockService();
$order = $factory->createOrderService();
$email = $factory->createEmailService();

$sku = 'console-ltd-123';

echo "Starting Checkout with email side effect...\n";

try {
    // 1. Local part (possibly via 2PC)
    // $stock->prepare($sku);
    // $order->prepare($sku);

    // 2. The external side effect (Email)
    $email->sendConfirmation('customer@test.com', $sku);

    // 3. Simulate an error BEFORE the final commit happens
    // throw new Exception("Crash before DB commit!");

    // $stock->commit();
    // $order->commit();

    echo "Success: Checkout including email completed.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "State: Email is out, but do we have an order in the DB?\n";
}
