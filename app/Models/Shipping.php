<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $fillable = [
        'Shipping_name',
        'Active',
        'Address',
        'Phonenumber'
    ];

    protected $hidden= [
        'created_at',
        'updated_at'
    ];
}
