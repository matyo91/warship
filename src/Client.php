<?php
declare(strict_types=1);

namespace Warship;

use Exception;
use Psr\Log\LoggerInterface;

class Client {
    const BOARD_WATER = 1;
    const BOARD_BOAT = 2;
    const BOARD_SHOT = 4;

    private array $boards;
    private array $lifes;
    private ?string $myShotCoord;

    public function __construct(private ?LoggerInterface $logger = null)
    {
        $this->reset();
    }

    /**
     * convert (X,Y) coords to ([A-J][1-10]) coords
     */
    public static function getCoord(int $x, int $y): string {
        return chr(65 + $x) . ($y + 1);
    }

    public function getBoard(string $player = 'my')
    {
        return $this->boards[$player];
    }

    public function displayBoard(string $player = 'my')
    {
        $display = "";
        for($i = 0; $i < 10; $i++) {
            for($j = 0; $j < 10; $j++) {
                $coord = $this->getCoord($i, $j);
                $display .= $this->boards[$player][$coord];
            }
            $display .= "\n";
        }
        $display .= "\n";

        if($this->logger) {
            $this->logger->info("board $player :\n" . $display);
        }

        return $display;
    }

    /**
     * reset game
     */
    public function reset(): void
    {
        $this->boards = [
            'my' => [],
            'ennemy' => []
        ];
        $this->lifes = [
            'my' => 0,
            'ennemy' => 0
        ];
        
        $this->myShotCoord = null;
        for($x = 0; $x < 10; $x++) {
            for($y = 0; $y < 10; $y++) {
                $coord = $this->getCoord($x, $y);
                $this->boards['my'][$coord] = self::BOARD_WATER;
                $this->boards['ennemy'][$coord] = self::BOARD_WATER;
            }
        }
    }

    /**
     * place boats on my board
     */
    public function setup(): void {
        $boatLengths = array(5, 4, 3, 3, 2);
        foreach($boatLengths as $boatLength) {
            do {
                $x = mt_rand(0, 9);
                $y = mt_rand(0, 9);
                $isHorizontal = mt_rand(0, 1) === 0;
            } while(!$this->canPlaceBoat($x, $y, $boatLength, $isHorizontal));

            $this->placeBoat($x, $y, $boatLength, $isHorizontal);
        }
    }

    public function canPlaceBoat(int $x, int $y, int $length, bool $isHorizontal): bool {
        for($i = 0; $i < $length; $i++) {
            $coord = $isHorizontal ? $this->getCoord($x, $y + $i) : $this->getCoord($x + $i, $y);
            if(!isset($this->boards['my'][$coord]) || $this->boards['my'][$coord] !== self::BOARD_WATER) {
                return false;
            }
        }

        return true;
    }

    public function placeBoat(int $x, int $y, int $length, bool $isHorizontal) {
        for($i = 0; $i < $length; $i++) {
            $coord = $isHorizontal ? $this->getCoord($x, $y + $i) : $this->getCoord($x + $i, $y);
            $this->boards['my'][$coord] = self::BOARD_BOAT;
            $this->lifes['my']++;
            $this->lifes['ennemy']++;
        }
    }

    /**
     * if shot coordinates are not provided, then algorithm find next coord as follow
     * - for any hit on board, then try adjacend coord.
     * - if no hit was found on board, then try all diagonal case coord at random.
     * - at last try case coord at random.
     */
    public function shot(string $coord = null): string {
        if($coord === null) {
            // random shot strategy
            do {
                $x = mt_rand(0, 9);
                $y = mt_rand(0, 9);
                $coord = $this->getCoord($x, $y);
            } while($this->boards['ennemy'][$coord] !== self::BOARD_WATER);
        }

        $this->boards['ennemy'][$coord] |= self::BOARD_SHOT;
        $this->myShotCoord = $coord;

        return $coord;
    }

    public function shotResponse($flag): string {
        $this->boards['ennemy'][$this->myShotCoord] |= self::BOARD_SHOT;
        $this->boards['ennemy'][$this->myShotCoord] |= $flag;

        if($flag === self::BOARD_BOAT) {
            $this->lifes['ennemy']--;
        }

        return "ok";
    }

    public function ennemyShot($coord): string {
        $this->boards['my'][$coord] |= self::BOARD_SHOT;

        if($this->boards['my'][$coord] & self::BOARD_WATER) {
            return 'miss';
        }

        $this->lifes['my']--;
        if($this->lifes['my'] > 0) {
            return 'hit';
        }

        return 'won';
    }

    public function handleCommand($command): string {
        if ($command === 'your turn') {
            return $this->shot();
        } elseif (preg_match('`^([A-J])(:?)([1-9]|10)$`i', $command, $m) === 1) {
            return $this->ennemyShot($m[1].$m[3]);
        } elseif ($command === 'miss') {
            return $this->shotResponse(self::BOARD_WATER);
        } elseif (preg_match('`^hit|sunk|won$`x', $command)) {
            return $this->shotResponse(self::BOARD_BOAT);
        }

        throw new Exception('command not found');
    }
}