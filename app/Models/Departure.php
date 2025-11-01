<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'departure_date',
        'delivery_address',
        'cart_type',
        'cart_items'
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'cart_items' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}