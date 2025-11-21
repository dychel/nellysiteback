<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address', // Remplace address_id par address
        'created_at',
        'is_paid',
        'stripe_session_id',
        'type', // Ajouté pour correspondre à votre code React
        'delivery_date' // Ajouté pour correspondre à votre code React
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'is_paid' => 'boolean',
        'delivery_date' => 'date' // Ajouté pour la date de livraison
    ];

    // Attribut calculé pour accéder plus facilement
    protected $appends = ['total'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Supprimer la relation address() puisque maintenant c'est un champ direct
    // public function address()
    // {
    //     return $this->belongsTo(Address::class);
    // }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getTotalAttribute()
    {
        return $this->orderDetails->sum('total');
    }

    // Méthode pour formater l'adresse si besoin
    public function getFormattedAddressAttribute()
    {
        return $this->address; // Ou un formatage spécifique si nécessaire
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
}