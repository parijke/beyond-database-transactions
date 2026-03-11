<?php

require_once __DIR__ . '/../src/autoload.php';

use App\ServiceFactory;

$factory = new ServiceFactory();
$inboxRepo = $factory->createInboxRepository();

// In the inbox, message IDs only need to be kept as long
// as delayed duplicates are expected (deduplication window).
// Common is e.g. 7 days.

// For the demo we delete everything older than 1 minute (if we have test data)
// or use the default of 7 days.

$interval = $argv[1] ?? '-7 days';

echo "Inbox Cleanup: Deleting entries older than '$interval'...\n";

$deletedCount = $inboxRepo->cleanup($interval);

echo "Inbox Cleanup: $deletedCount entries deleted.\n";
