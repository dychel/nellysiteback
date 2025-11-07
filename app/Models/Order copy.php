<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'created_at',
        'is_paid',
        'stripe_session_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'is_paid' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getTotalAttribute()
    {
        return $this->orderDetails->sum('total');
    }
}