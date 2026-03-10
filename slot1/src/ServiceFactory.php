<?php

declare(strict_types=1);

namespace App;

use PDO;

class ServiceFactory {
    public function createStockService(): StockService {
        return new StockService($this->createPDO(__DIR__ . '/../stock.db'));
    }

    public function createOrderService(): OrderService {
        return new OrderService($this->createPDO(__DIR__ . '/../order.db'));
    }

    public function createEmailService(): EmailService {
        return new EmailService();
    }

    public function createMonolithPDO(): PDO {
        return $this->createPDO(__DIR__ . '/../monolith.db');
    }

    private function createPDO(string $path): PDO {
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
