<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageVarient extends Model
{
    use HasFactory;

    protected $table = 'package_product'; // Explicitly define the table name

    protected $fillable = ['package_id', 'varient_id', 'quantity'];

    /**
     * Relationship: A package variant belongs to a package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Relationship: A package variant belongs to a variant.
     */
    public function varient()
    {
        return $this->belongsTo(Varient::class, 'varient_id');
    }
}

