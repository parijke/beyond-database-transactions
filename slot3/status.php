<?php

require __DIR__ . '/src/autoload.php';

use App\ServiceFactory;
use App\StatusPrinter;

$factory = new ServiceFactory();
$printer = new StatusPrinter();

$printer->printOrderService($factory->getOrderPdo());
