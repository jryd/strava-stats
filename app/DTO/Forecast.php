<?php

namespace App\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Forecast extends DataTransferObject
{
    public Weather $currently;
}
