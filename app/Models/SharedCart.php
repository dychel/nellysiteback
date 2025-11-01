<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SharedCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_paid'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->token = Str::random(32);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SharedCartItem::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(SharedCartPayment::class, SharedCartItem::class);
    }
}