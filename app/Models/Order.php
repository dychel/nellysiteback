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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    // Dans App\Models\Order
    public function departure()
    {
        return $this->hasOne(Departure::class, 'order_id');
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

    // Accessor pour le type de calendrier formaté
    public function getCalendarTypeFormattedAttribute()
    {
        return $this->calendar_type === 'jour' ? 'Journalier' : 'Hebdomadaire';
    }

    // Accessor pour le type de repas formaté
    public function getMealTypeFormattedAttribute()
    {
        return match($this->meal_type) {
            'chaud' => 'Repas chaud',
            'froid' => 'Repas froid',
            'tous' => 'Tous les repas',
            default => 'Non spécifié'
        };
    }

    // Accessor pour la méthode de paiement formatée
    public function getPaymentMethodFormattedAttribute()
    {
        return match($this->payment_method) {
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'transfer' => 'Virement bancaire',
            'cash' => 'Paiement en espèces',
            default => 'Non spécifié'
        };
    }

    // Accessor pour le numéro de commande formaté
    public function getOrderNumberAttribute()
    {
        return 'CMD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    // Méthode pour vérifier si la commande peut être annulée
    public function canBeCancelled()
    {
        return !$this->is_paid && now()->diffInHours($this->created_at) < 24;
    }
}