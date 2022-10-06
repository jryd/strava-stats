<?php

namespace App\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Weather extends DataTransferObject
{
    public string $summary;
    public float $temperature;
    public float $apparentTemperature;
    public float $humidity;
    public float $windSpeed;
    public int $windBearing;
}
