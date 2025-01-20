<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // One Cart has many CartItems
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
