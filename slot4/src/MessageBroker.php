<?php

namespace App;

class MessageBroker
{
    private string $file;

    public function __construct()
    {
        $this->file = __DIR__ . '/../broker.db';
    }

    public function publish(string $eventType, array $payload): void
    {
        $data = [
            'type' => $eventType,
            'payload' => $payload,
            'timestamp' => microtime(true)
        ];
        file_put_contents($this->file, json_encode($data) . "\n", FILE_APPEND | LOCK_EX);
        echo "[Broker] Published $eventType\n";
    }

    public function subscribe(string $eventType, callable $callback): void
    {
        echo "[Broker] Listening for $eventType...\n";
        $lastSize = 0;
        if (file_exists($this->file)) {
            $lastSize = filesize($this->file);
        }

        while (true) {
            clearstatcache();
            if (file_exists($this->file)) {
                $currentSize = filesize($this->file);
                if ($currentSize > $lastSize) {
                    $handle = fopen($this->file, 'r');
                    fseek($handle, $lastSize);
                    while (($line = fgets($handle)) !== false) {
                        $event = json_decode($line, true);
                        if ($event['type'] === $eventType) {
                            $callback($event['payload']);
                        }
                    }
                    $lastSize = ftell($handle);
                    fclose($handle);
                }
            }
            usleep(200000);
        }
    }
}
