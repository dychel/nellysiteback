<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedCartPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cart_item_id',
        'quantity',
        'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItem()
    {
        return $this->belongsTo(SharedCartItem::class, 'cart_item_id');
    }
}