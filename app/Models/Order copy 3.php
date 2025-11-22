<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'type',
        'delivery_date',
        'meal_type',
        'calendar_type',
        'payment_method',
        'is_paid',
        'stripe_session_id',
        'total',
        'notes',
        'order_date'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'delivery_date' => 'date',
        'is_paid' => 'boolean',
        'total' => 'decimal:2'
    ];

    // Supprimer l'attribut calculé total puisque c'est maintenant un champ direct
    // protected $appends = ['total'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Méthode pour formater l'adresse si besoin
    public function getFormattedAddressAttribute()
    {
        return $this->address;
    }

    // Scope pour les commandes payées
    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // Scope pour les commandes en attente
    public function scopePending($query)
    {
        return $query->where('is_paid', false);
    }

    // Méthode pour marquer comme payée
    public function markAsPaid($stripeSessionId = null)
    {
        $this->update([
            'is_paid' => true,
            'stripe_session_id' => $stripeSessionId
        ]);
    }

    // Nouvelle méthode pour calculer le total à partir des détails
    public function calculateTotal()
    {
        return $this->orderDetails->sum('total');
    }
}