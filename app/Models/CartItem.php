<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{

    protected $fillable = [
        'cart_id',      // Foreign key to the cart
        'varient_id',   // Foreign key to the variant
        'quantity',     // Quantity of the variant
        'price',        // Price of the variant
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // One CartItem belongs to one Variant
    public function varient()
    {
        return $this->belongsTo(Varient::class);
    }

    public function product()
    {
        return $this->variant->product();  // Accessing the product from the related variant
    }
}
