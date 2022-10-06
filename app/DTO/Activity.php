<?php

namespace App\DTO;

use Spatie\DataTransferObject\DataTransferObject;

class Activity extends DataTransferObject
{
    public string $description;
    public string $start_date;
    public float $start_latitude;
    public float $start_longitude;
 }
