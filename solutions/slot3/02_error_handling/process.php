<?php

require_once __DIR__ . '/../../../slot3/src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

echo "Starte Inbox Worker (Lösung)...\n";

while ($message = $inboxRepo->fetchNextPending()) {
    echo "Verarbeite Nachricht #{$message['id']} (Type: {$message['event_type']}, Aggregate: {$message['aggregate_id']})...\n";

    $data = json_decode($message['payload'], true);

    // AUFGABE 2: Aggregate Blocking
    if ($inboxRepo->hasFailedPredecessor($message['aggregate_id'], $message['id'])) {
        echo "  [BLOCK] Aggregat {$message['aggregate_id']} ist blockiert. Parke Nachricht.\n";
        $inboxRepo->markAsBlocked($message['id']);
        continue;
    }

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
