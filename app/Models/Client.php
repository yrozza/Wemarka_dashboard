<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
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
        'area_name',
        'city_name',
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
        return $this->hasMany(Order::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }
}
