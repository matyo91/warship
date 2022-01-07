<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Warship\Client;

final class ClientTest extends TestCase
{
    /**
     * @dataProvider coordsProvider
     */
    public function testCoords($x, $y, $coord): void
    {
        $clientCoord = Client::getCoord($x, $y);
        $this->assertEquals($coord, $clientCoord);
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
