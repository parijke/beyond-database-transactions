<?php

declare(strict_types=1);

namespace App;

class EmailService {
    public function sendConfirmation(string $email, string $sku): void {
        echo "[EmailService] Sending confirmation for $sku to $email...\n";
        // Simulate network latency
        usleep(500000);
        echo "[EmailService] Email successfully sent!\n";
    }
}
