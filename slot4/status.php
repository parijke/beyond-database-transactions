<?php

require_once __DIR__ . '/src/autoload.php';

use App\ServiceFactory;
use App\StatusPrinter;

$factory = new ServiceFactory();
$printer = new StatusPrinter();

echo "--- ORDER SERVICE ---\n";
$printer->printTable($factory->getOrderPdo(), 'orders', 'Orders');
$printer->printTable($factory->getOrderPdo(), 'outbox', 'Outbox (Events)');
$printer->printTable($factory->getOrderPdo(), 'inbox', 'Inbox (Idempotency)');

echo "\n--- STOCK SERVICE ---\n";
$printer->printTable($factory->getStockPdo(), 'stock_reservations', 'Reservations');
$printer->printTable($factory->getStockPdo(), 'outbox', 'Outbox (Events)');
$printer->printTable($factory->getStockPdo(), 'inbox', 'Inbox (Idempotency)');

echo "\n--- PAYMENT SERVICE ---\n";
$printer->printTable($factory->getPaymentPdo(), 'payments', 'Payments');
$printer->printTable($factory->getPaymentPdo(), 'outbox', 'Outbox (Events)');
$printer->printTable($factory->getPaymentPdo(), 'inbox', 'Inbox (Idempotency)');
