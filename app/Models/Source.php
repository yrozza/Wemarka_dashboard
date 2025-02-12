<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'source_name', // Renamed to follow Laravel conventions
        'active'
    ];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
