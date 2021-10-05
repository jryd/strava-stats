<?php

namespace App\Services;

class WindDirection
{
    private array $compass = [
        'N',
        'NNE',
        'NE',
        'ENE',
        'E',
        'ESE',
        'SE',
        'SSE',
        'S',
        'SSW',
        'SW',
        'WSW',
        'W',
        'WNW',
        'NW',
        'NNW',
    ];

    public function fromBearing($bearing)
    {
        return $this->compass[round($bearing / 22.5) % 16];
    }
}
