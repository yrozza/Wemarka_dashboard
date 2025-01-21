<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'cart_id',      // Foreign key to the cart
        'varient_id',   // Foreign key to the variant
        'quantity',     // Quantity of the variant
        'price',        // Price of the variant
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class);
    }

    // One Cart has many CartItems
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
