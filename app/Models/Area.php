<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = [
        'name',
        'shipping_price',
        'Area_name',
        'Price',
        'active',
        'city_id', // The foreign key for the city
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function city(){
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
