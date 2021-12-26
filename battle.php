<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Warship\Game;

$game = new Game();
while (true) {
    $command = trim(fgets(STDIN));
    $game->handleCommand($command);
}