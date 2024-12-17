<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'Area_name',
        'Active',
        'Price'
    ];

    public $timestamps = false;
}
