<?php

namespace App;

use PDO;

class ServiceFactory
{
    private ?PDO $orderPdo = null;
    private ?PDO $stockPdo = null;
    private ?PDO $paymentPdo = null;

    public function getOrderPdo(): PDO
    {
        if ($this->orderPdo === null) {
            $this->orderPdo = new PDO('sqlite:' . __DIR__ . '/../order.db');
            $this->orderPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->orderPdo;
    }

    public function getStockPdo(): PDO
    {
        if ($this->stockPdo === null) {
            $this->stockPdo = new PDO('sqlite:' . __DIR__ . '/../stock.db');
            $this->stockPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->stockPdo;
    }

    public function getPaymentPdo(): PDO
    {
        if ($this->paymentPdo === null) {
            $this->paymentPdo = new PDO('sqlite:' . __DIR__ . '/../payment.db');
            $this->paymentPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->paymentPdo;
    }

    public function createOrderService(): OrderService
    {
        return new OrderService($this->getOrderPdo());
    }

    public function createStockService(): StockService
    {
        return new StockService($this->getStockPdo());
    }

    public function createPaymentService(): PaymentService
    {
        return new PaymentService($this->getPaymentPdo());
    }

    public function createMessageBroker(): MessageBroker
    {
        return new MessageBroker();
    }

    public function createOutboxRepository(PDO $pdo): OutboxRepository
    {
        return new OutboxRepository($pdo);
    }

    public function createInboxRepository(PDO $pdo): InboxRepository
    {
        return new InboxRepository($pdo);
    }

    public function createCheckpointRepository(PDO $pdo): CheckpointRepository
    {
        return new CheckpointRepository($pdo);
    }
}
