<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false ;
    
    protected $fillable = [
        'City_name',
        'Active',
    ];

    protected $hidden = [
        'Created_at',
        'Updated_at'
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
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
