<?php

declare(strict_types=1);

namespace App;

use PDO;

class StatusPrinter {
    public function printTable(string $title, string $dbFile, string $query): void {
        if (!file_exists($dbFile)) {
            return;
        }

        echo "=== $title (" . basename($dbFile) . ") ===\n";
        $pdo = new PDO('sqlite:' . $dbFile);
        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "No data available.\n\n";
            return;
        }

        // Header
        $headers = array_keys($rows[0]);
        echo implode("\t| ", $headers) . "\n";
        echo str_repeat("-", 40) . "\n";

        foreach ($rows as $row) {
            echo implode("\t| ", $row) . "\n";
        }
        echo "\n";
    }
}
