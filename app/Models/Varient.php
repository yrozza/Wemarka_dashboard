<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Varient extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = [
        'color',
        'volume',
        'varient',
        'Pcode',
        'weight',
        'price',
        'product_image',
        'stock',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // One Variant can be part of many OrderItems
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
