<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{


    protected $hidden = ['created_at', 'updated_at'];


        protected $fillable = [
        'product_name',
        'product_description',
        'brand_id',
        'category_id'
    ];
    
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Varient::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_product')->withPivot('quantity');
    }
}
