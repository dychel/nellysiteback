<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'postal_code',
        'city',
        'country'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}