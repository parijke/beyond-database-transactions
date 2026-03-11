<?php declare(strict_types=1);
namespace App;

class WebOrderService implements OrderService {

    private ?string $currentSKU;

    public function __construct(
        private readonly WebServiceOfChoice $service
    ) {
    }

    public function createOrder(string $sku): void {
        // TODO: Implement createOrder() method.
    }

    public function prepare(string $sku): bool {
        $this->currentSKU = $sku;
        return $this->service->verifyIsAvailable();
    }

    public function commit(): void {
        $this->service->createFinalOrder($this->currentSKU);
        $this->currentSKU = null;
    }

}
