<?php

declare(strict_types=1);

namespace App;

class EmailService {
    public function sendConfirmation(string $email, string $sku): void {
        echo "[EmailService] Sende Bestätigung für $sku an $email...\n";
        // Simuliere Netzwerk-Latenz
        usleep(500000);
        echo "[EmailService] E-Mail erfolgreich versendet!\n";
    }
}
