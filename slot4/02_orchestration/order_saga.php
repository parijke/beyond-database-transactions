<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$orderService = $factory->createOrderService();
$stockService = $factory->createStockService();
$paymentService = $factory->createPaymentService();

$orderId = (int)($argv[1] ?? 1);
$amount = (int)($argv[2] ?? 100);

echo "Saga Orchestrator started for Order #$orderId...\n";

// In orchestration, this one process controls the sequence.
// In this training, we use the synchronous variant (request/response).
// It calls the services (simulated locally, in reality often via API/RPC).
// Since the services work on different databases, we have no
// atomic transaction and must manually compensate for errors.

try {
    // Step 1: Create Order
    echo "Step 1: Create Order...\n";
    $orderService->createOrder($orderId, $amount);

    // Step 2: Reserve Stock
    echo "Step 2: Reserve Stock...\n";
    $success = $stockService->reserve($orderId);

    if (!$success) {
        echo "ERROR: Stock not available.\n";
        $orderService->updateStatus($orderId, 'OUT_OF_STOCK');
        exit;
    }

    // Step 3: Execute Payment
    echo "Step 3: Execute Payment...\n";
    $paid = $paymentService->charge($orderId, $amount);

    if (!$paid) {
        echo "ERROR: Payment failed. Starting compensation...\n";

        $stockService->release($orderId);
        $orderService->updateStatus($orderId, 'PAYMENT_FAILED');
        exit;
    }

    // Step 4: Success
    echo "Step 4: Finalization...\n";
    $orderService->updateStatus($orderId, 'SUCCESS');
    echo "Order #$orderId completed successfully!\n";

} catch (Exception $e) {
    echo "Critical error in the saga: " . $e->getMessage() . "\n";
}
