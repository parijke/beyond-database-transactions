<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$outboxRepo = $factory->createOutboxRepositoryDeleting();
$broker = $factory->createMessageBroker();

echo "Relay-Worker (Delete-after-Dispatch) startet...\n";

// 1. Alle vorhandenen Events laden (da erledigte gelöscht werden)
$events = $outboxRepo->fetchPendingEvents();

if (empty($events)) {
    echo "Keine neuen Events zum Verarbeiten.\n";
    exit;
}

foreach ($events as $event) {
    echo "Verarbeite Event #{$event['id']} ({$event['event_type']})...\n";

    try {
        // a) Event an den Broker senden
        $broker->publish($event['event_type'], json_decode($event['payload'], true));
        
        // b) Aus der Outbox löschen
        $outboxRepo->remove($event['id']);
        
        echo "Event #{$event['id']} erfolgreich versendet und gelöscht.\n";
    } catch (\Exception $e) {
        echo "Fehler bei Event #{$event['id']}: " . $e->getMessage() . "\n";
        break;
    }
}
