<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // One OrderItem belongs to one Variant
    public function varient()
    {
        return $this->belongsTo(Varient::class);
    }
}
