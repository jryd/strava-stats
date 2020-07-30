<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcessedActivity extends Model
{
    protected $guarded = [];

    protected $dates = [
        'recorded_at',
        'created_at',
        'updated_at',
    ];
}
