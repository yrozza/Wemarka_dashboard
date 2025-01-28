<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',       
        'cart_id',         
        'varient_id',
        'status',         
        'shipping_status', 
        'total_price',  
        'Address',   
        'client_notes',
        'area_id',
        'city_id',
        'area_name',
        'city_name',  
        'client_name',
        'client_phone',
        'additional_phone'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class); // Order belongs to one client
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

