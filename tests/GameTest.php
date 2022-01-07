<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Warship\Game;

final class GameTest extends TestCase
{
    /**
     * @dataProvider coordsProvider
     */
    public function testCoords($x, $y, $coord): void
    {
        $game = new Game();
        $gameCoord = $game->getCoord($x, $y);
        $this->assertEquals($coord, $gameCoord);
    }

    public function coordsProvider()
    {
        return [
            [0, 0, 'A1'],
            [0, 4, 'A5'],
            [3, 5, 'D6'],
            [4, 8, 'E9'],
            [5, 9, 'F10'],
            [9, 0, 'J1'],
        ];
    }
}
