<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Warship\Client;

final class ClientTest extends TestCase
{
    protected Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    /**
     * @dataProvider coordsProvider
     */
    public function testCoords($x, $y, $coord): void
    {
        $clientCoord = $this->client->getCoord($x, $y);
        $this->assertEquals($coord, $clientCoord);
    }

    /**
     * @dataProvider setupsProvider
     */
    public function testPlacement(array $boats)
    {
        foreach($boats as $boat) {
            [$x, $y, $length, $isHorizontal, $coords] = $boat;
            $this->client->placeBoat($x, $y, $length, $isHorizontal);

            $board = $this->client->getBoard();
            foreach($coords as $coord) {
                $this->assertArrayHasKey($coord, $board);
                $this->assertEquals(Client::BOARD_BOAT, $board[$coord]);
            }
        }
    }

    public function setupsProvider()
    {
        return [
            [
                [
                    [0, 0, 2, true, ['A1', 'B1']]
                ]
            ]
        ];
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
