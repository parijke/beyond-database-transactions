<?php declare(strict_types=1);
namespace App;

interface OrderService {

    public function createOrder(string $sku): void;

    public function prepare(string $sku): bool;

    public function commit(): void;
}
