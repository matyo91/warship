<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Warship\Client;

$client = new Client();
while (true) {
    $command = trim(fgets(STDIN));
    echo $client->handleCommand($command) . "\n";
}