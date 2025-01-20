<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class); // Order belongs to one client
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

