<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    use HasFactory;


    protected $fillable = [
        'client_name',
        'client_age',
        'client_email',
        'client_phonenumber',
        'area_id',
        'city_id',
        'source_id',
        'source_link',
        'area_name',  // Add this
        'city_name',  // Add this
    ];

    public function area()
    {
        return $this->belongsTo(Area::class); 
    }

    
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class); // Client has many orders
    }

    public function sources(){
        return $this->belongsTo(source::class); 
    }
}
