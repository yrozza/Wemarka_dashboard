<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // One CartItem belongs to one Variant
    public function varient()
    {
        return $this->belongsTo(Varient::class);
    }
}
