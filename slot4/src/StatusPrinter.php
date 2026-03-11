<?php

namespace App;

use PDO;

class StatusPrinter
{
    public function printOrderService(PDO $orderPdo): void
    {
        echo "\n--- Order Service ---\n";
        $this->printTable($orderPdo, 'orders', 'Orders');
        $this->printTable($orderPdo, 'outbox', 'Outbox (Events)');
        $this->printTable($orderPdo, 'checkpoints', 'Checkpoints');
        $this->printTable($orderPdo, 'inbox', 'Inbox (Idempotency)');
    }

    public function printTable(PDO $pdo, string $table, string $label): void
    {
        echo "[$label]\n";
        $stmt = $pdo->query("SELECT * FROM $table");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "Keine Daten vorhanden.\n\n";
            return;
        }

        $columns = array_keys($rows[0]);
        $widths = [];
        foreach ($columns as $col) {
            $widths[$col] = strlen($col);
        }
        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $widths[$col] = max($widths[$col], strlen((string)$row[$col]));
            }
        }

        // Header
        foreach ($columns as $col) {
            echo "| " . str_pad($col, $widths[$col]) . " ";
        }
        echo "|\n";

        // Separator
        foreach ($columns as $col) {
            echo "|-" . str_repeat("-", $widths[$col]) . "-";
        }
        echo "|\n";

        // Rows
        foreach ($rows as $row) {
            foreach ($columns as $col) {
                echo "| " . str_pad((string)$row[$col], $widths[$col]) . " ";
            }
            echo "|\n";
        }
        echo "\n";
    }
}
