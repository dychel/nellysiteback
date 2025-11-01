<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shared_cart_id',
        'product_id',
        'name',
        'price',
        'quantity',
        'remaining_quantity',
        'illustration',
        'subtitle',
        'description',
        'weight_kg'
    ];

    protected $attributes = [
        'weight_kg' => 1,
    ];

    public function sharedCart()
    {
        return $this->belongsTo(SharedCart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function payments()
    {
        return $this->hasMany(SharedCartPayment::class, 'cart_item_id');
    }

    public function getTotalAttribute()
    {
        return $this->price * $this->quantity * $this->weight_kg;
    }
}