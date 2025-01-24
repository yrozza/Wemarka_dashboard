<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',       // The ID of the client placing the order
        'cart_id',         // The cart associated with the order
        'varient_id',
        'status',          // The status of the order (e.g., pending, completed)
        'shipping_status', // The shipping status of the order
        'total_price',     // The total price of the order
    ];

    public function client()
    {
        return $this->belongsTo(Client::class); // Order belongs to one client
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

