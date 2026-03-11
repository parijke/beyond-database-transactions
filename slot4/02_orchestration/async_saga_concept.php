<?php

/**
 * CONCEPT SKETCH: Asynchronous Orchestration
 *
 * This file is not executable but illustrates how the synchronous
 * orchestrator from 'order_saga.php' would look in an asynchronous,
 * event-driven environment.
 */

class OrderSagaOrchestrator
{
    private $stateRepository; // Persistent storage of the saga state
    private $messageBroker;   // For sending commands

    public function handle(Event $event): void {
        switch ($event->type) {
            case 'OrderCreated':
                assert($event instanceof OrderCreated);
                $this->onOrderCreated($event);
                break;
            case 'StockReserved':
                assert($event instanceof StockReserved);
                $this->onStockReserved($event);
                break;
            case 'PaymentFailed':
                //assert(...)
                $this->onPaymentFailed($event);
                break;
            case 'StockReleased':
                //assert(...)h
                $this->orchestrator->onStockReleased($event);
                break;
        }
}

    /**
     * Entry point: The saga is started by an event (e.g., OrderCreated).
     */
    public function onOrderCreated(OrderCreated $event)
    {
        // 1. Initialize saga status
        $sagaId = $event->orderId;
        $this->stateRepository->save($sagaId, [
            'status' => 'STARTING',
            'orderId' => $event->orderId,
            'amount' => $event->amount
        ]);

        // 2. Send command to the Stock Service
        $this->messageBroker->publish('reserve_stock_command', [
            'orderId' => $event->orderId,
            'sagaId' => $sagaId
        ]);

        $this->stateRepository->updateStatus($sagaId, 'AWAITING_STOCK');
    }

    /**
     * Reaction to the response from the Stock Service
     */
    public function onStockReserved(StockReserved $event)
    {
        $state = $this->stateRepository->find($event->sagaId);

        if ($state['status'] !== 'AWAITING_STOCK') return;

        // Next step: Request payment
        $this->messageBroker->publish('charge_payment_command', [
            'orderId' => $state['orderId'],
            'amount' => $state['amount'],
            'sagaId' => $event->sagaId
        ]);

        $this->stateRepository->updateStatus($event->sagaId, 'AWAITING_PAYMENT');
    }

    /**
     * Error handling / Compensation (Asynchronous)
     */
    public function onPaymentFailed(PaymentFailed $event)
    {
        $state = $this->stateRepository->find($event->sagaId);

        echo "Payment failed. Starting asynchronous compensation...\n";

        // Send compensation command
        $this->messageBroker->publish('release_stock_command', [
            'orderId' => $state['orderId'],
            'sagaId' => $event->sagaId
        ]);

        $this->stateRepository->updateStatus($event->sagaId, 'COMPENSATING_STOCK');
    }

    /**
     * Completion of compensation
     */
    public function onStockReleased(StockReleased $event)
    {
        $this->stateRepository->updateStatus($event->sagaId, 'FAILED_AND_COMPENSATED');
        echo "Saga for Order #{$event->sagaId} aborted cleanly.\n";
    }
}

/**
 * THE SAGA WORKER (The Engine)
 *
 * This worker runs as a background process, reads events from the inbox
 * (or directly from the broker) and delegates them to the orchestrator.
 */
class AsyncSagaWorker
{
    private $orchestrator;
    private $inboxRepository;

    public function run()
    {
        while (true) {
            $event = $this->inboxRepository->fetchNextPending();
            if (!$event) {
                usleep(500000);
                continue;
            }

            $this->orchestrator->handle($event);
            $this->inboxRepository->markAsProcessed($event->id);
        }
    }
}

/*
 * THE DIFFERENCE TO THE SYNCHRONOUS VARIANT:
 *
 * 1. No blocking calls: The process does not wait for the DB or API.
 * 2. State management: Since the PHP process is terminated between steps,
 *    the progress ('status') must be stored in a database (State Repository).
 * 3. Correlation: Every message needs a 'sagaId' to be able to assign the response
 *    to the correct process run.
 * 4. Resilience: If the orchestrator crashes, the messages are still in the
 *    broker and the state in the DB. It can simply be continued.
 */
