<?php

declare(strict_types=1);

use App\StatusPrinter;

require __DIR__ . '/../src/autoload.php';

$printer = new StatusPrinter();

$printer->printTable("Monolith Inventory", __DIR__ . '/../monolith.db', "SELECT * FROM inventory");
$printer->printTable("Monolith Orders", __DIR__ . '/../monolith.db', "SELECT * FROM orders");
