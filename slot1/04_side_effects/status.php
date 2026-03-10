<?php

declare(strict_types=1);

use App\StatusPrinter;

require __DIR__ . '/../src/autoload.php';

$printer = new StatusPrinter();

$printer->printTable("Stock Service Inventory", __DIR__ . '/../stock.db', "SELECT * FROM inventory");
$printer->printTable("Order Service Orders", __DIR__ . '/../order.db', "SELECT * FROM orders");
