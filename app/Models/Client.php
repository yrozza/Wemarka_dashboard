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
        'client_area',
        'client_city',
        'client_email',
        'client_phonenumber',
        'source_id'
    ];

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
