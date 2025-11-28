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

    // Relation modifiée pour utiliser le champ 'address' textuel
    public function orders()
    {
        return Order::where('address', $this->address);
    }

    // Accessor pour le comptage des commandes
    public function getOrdersCountAttribute()
    {
        return Order::where('address', $this->address)->count();
    }
}