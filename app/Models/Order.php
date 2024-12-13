<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'price');
    }

    public function client()
    {
        return $this->belongsTo(Client::class); // Order belongs to one client
    }
}

