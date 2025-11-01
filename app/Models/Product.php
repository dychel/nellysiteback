<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'illustration',
        'subtitle',
        'description',
        'price',
        'weight_kg',
        'category_id',
        'region_id'
    ];

    protected $attributes = [
        'weight_kg' => 1,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favori::class);
    }

    public function sharedCartItems()
    {
        return $this->hasMany(SharedCartItem::class);
    }
}