<?php

namespace Tests\Unit;

use App\Services\WindDirection;
use PHPUnit\Framework\TestCase;

class WindDirectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider bearings
     */
    public function it_can_calculate_the_wind_direction_from_a_bearing($bearing, $direction)
    {
        $this->assertEquals($direction, (new WindDirection())->fromBearing($bearing));
    }

    public function bearings()
    {
        return [
            'Under the closest' => [
                '359',
                'N',
            ],
            'Over the closest' => [
                '204',
                'SSW',
            ],
            'Exactly' => [
                '180',
                'S',
            ],
        ];
    }
}
