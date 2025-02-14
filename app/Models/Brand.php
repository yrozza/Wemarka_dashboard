<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $fillable =[
        'Brand_name',
        'Active',
        'Company_name'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
