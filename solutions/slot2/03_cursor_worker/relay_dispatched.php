<?php

require __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$outboxRepo = $factory->createOutboxRepositoryDispatched();
$broker = $factory->createMessageBroker();

echo "Relay-Worker (Dispatched-Mark) startet...\n";

// 1. Neue Events laden (alle ohne dispatched_at)
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

        // b) Als versendet markieren
        $outboxRepo->markAsDispatched($event['id']);

        echo "Event #{$event['id']} erfolgreich versendet und markiert.\n";
    } catch (\Exception $e) {
        echo "Fehler bei Event #{$event['id']}: " . $e->getMessage() . "\n";
        // Wir brechen hier ab oder machen mit dem nächsten weiter,
        // je nachdem wie fehlertolerant wir sein wollen.
        break;
    }
}
