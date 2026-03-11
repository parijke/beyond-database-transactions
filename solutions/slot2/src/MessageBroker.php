<?php

namespace App;

class MessageBroker
{
    public function publish(string $eventType, array $payload): void
    {
        echo sprintf(
            "[MessageBroker] Sent Event: %s | Payload: %s\n",
            $eventType,
            json_encode($payload)
        );
    }
}
