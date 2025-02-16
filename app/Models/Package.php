<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = ['name','price','description'];

    public function packageProducts()
    {
        return $this->hasMany(PackageVarient::class, 'package_id');
    }

    public function varients()
    {
        return $this->hasManyThrough(Varient::class, PackageVarient::class, 'package_id', 'id', 'id', 'varient_id');
    }
}


