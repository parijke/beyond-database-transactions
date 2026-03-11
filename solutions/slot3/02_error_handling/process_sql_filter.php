<?php

require_once __DIR__ . '/../../../slot3/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

echo "Starte Inbox Worker (SQL Filter Lösung)...\n";

while ($message = $inboxRepo->fetchNextUnblockedPending()) {
    echo "Verarbeite Nachricht #{$message['id']} (Type: {$message['event_type']}, Aggregate: {$message['aggregate_id']})...\n";

    $data = json_decode($message['payload'], true);

    try {
        // Simuliere Business Logik
        if (isset($data['cause_error']) && $data['cause_error'] === true) {
            throw new Exception("Simulierter schwerer Fehler bei der Verarbeitung!");
        }

        echo "  [OK] Nachricht erfolgreich verarbeitet.\n";
        $inboxRepo->markAsProcessed($message['id']);

    } catch (Exception $e) {
        echo "  [ERROR] Fehler: " . $e->getMessage() . "\n";
        $inboxRepo->markAsFailed($message['id'], $e->getMessage());
    }
}

echo "Keine weiteren Nachrichten.\n";
