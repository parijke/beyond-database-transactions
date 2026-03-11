<?php

namespace App;

use PDO;

class ServiceFactory
{
    private ?PDO $orderPdo = null;

    public function getOrderPdo(): PDO
    {
        if ($this->orderPdo === null) {
            $this->orderPdo = new PDO('sqlite:' . __DIR__ . '/../order.db');
            $this->orderPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->orderPdo;
    }

    public function createOrderService(): OrderService
    {
        return new PdoOrderService($this->getOrderPdo());
    }

    public function createMessageBroker(): MessageBroker
    {
        return new MessageBroker();
    }

    public function createOutboxRepository(): OutboxRepository
    {
        return new OutboxRepository($this->getOrderPdo());
    }

    public function createOutboxRepositoryDispatched(): OutboxRepositoryDispatched
    {
        return new OutboxRepositoryDispatched($this->getOrderPdo());
    }

    public function createOutboxRepositoryDeleting(): OutboxRepositoryDeleting
    {
        return new OutboxRepositoryDeleting($this->getOrderPdo());
    }

    public function createCheckpointRepository(): CheckpointRepository
    {
        return new CheckpointRepository($this->getOrderPdo());
    }

    public function createInboxRepository(): InboxRepository
    {
        return new InboxRepository($this->getOrderPdo());
    }
}
