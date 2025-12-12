<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'departure_date',
        'delivery_address',
        'payment_method',
        'notes',
        'total',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'cart_type',
        'cart_items'
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'cart_items' => 'array',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'status_formatted',
        'payment_method_formatted'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessor pour le statut formaté
    public function getStatusFormattedAttribute()
    {
        return match($this->status ?? 'pending') {
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'preparing' => 'En préparation',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
            default => 'Inconnu'
        };
    }

    // Accessor pour la méthode de paiement formatée
    public function getPaymentMethodFormattedAttribute()
    {
        return match($this->payment_method ?? 'cash') {
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'transfer' => 'Virement bancaire',
            'cash' => 'Paiement en espèces',
            default => 'Non spécifié'
        };
    }

    // Méthode pour obtenir l'ID formaté (remplace departure_number)
    public function getFormattedIdAttribute()
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}