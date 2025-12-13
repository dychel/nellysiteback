<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guide extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'pour_qui',
        'pour_quand',
        'preferences',
        'panier_type',
        'selected_products',
        'total',
        'delivery_address',
        'payment_method',
        'user_info',
        'current_step',
        'completed',
        'order_id'
    ];

    protected $casts = [
        'preferences' => 'array',
        'selected_products' => 'array',
        'user_info' => 'array',
        'total' => 'decimal:2',
        'completed' => 'boolean'
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec la commande
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessor pour le total formaté
    public function getTotalFormattedAttribute()
    {
        return number_format($this->total, 2, ',', ' ') . ' €';
    }

    // Méthode pour marquer comme complété
    public function markAsCompleted($orderId = null)
    {
        $this->update([
            'completed' => true,
            'order_id' => $orderId,
            'current_step' => 8
        ]);
    }

    // Méthode pour sauvegarder l'étape actuelle - CORRIGÉE
    public function saveStep($step, $data)
    {
        \Log::info('Saving guide step', [
            'step' => $step,
            'guide_id' => $this->id,
            'data_keys' => array_keys($data)
        ]);

        $updateData = ['current_step' => $step];
        
        // Extraire les données du champ 'data' si présent
        $stepData = $data['data'] ?? $data;
        
        // Mettre à jour les champs spécifiques selon l'étape
        switch($step) {
            case 1:
                $updateData['pour_qui'] = $stepData['pourQui'] ?? $stepData['pour_qui'] ?? null;
                break;
            case 2:
                $updateData['pour_quand'] = $stepData['pourQuand'] ?? $stepData['pour_quand'] ?? null;
                break;
            case 3:
                $updateData['preferences'] = $stepData['preferences'] ?? null;
                break;
            case 4:
                $updateData['panier_type'] = $stepData['panierType'] ?? $stepData['panier_type'] ?? null;
                $updateData['selected_products'] = $stepData['selectedProducts'] ?? $stepData['selected_products'] ?? null;
                
                // Calculer le total
                $total = 0;
                $selectedProducts = $updateData['selected_products'];
                
                if (is_array($selectedProducts)) {
                    foreach ($selectedProducts as $item) {
                        // Support pour les deux formats
                        $price = $item['product']['price'] ?? $item['price'] ?? 0;
                        $quantity = $item['quantity'] ?? 1;
                        // S'assurer que l'illustration est incluse
                        if (!isset($item['product']['illustration']) && !isset($item['illustration'])) {
                            // Si c'est un produit de la base de données, charger l'illustration
                            $productId = $item['product']['id'] ?? $item['id'] ?? null;
                            if ($productId) {
                                $product = \App\Models\Product::find($productId);
                                if ($product && $product->illustration) {
                                    // Ajouter l'illustration aux données
                                    if (isset($item['product'])) {
                                        $item['product']['illustration'] = $product->illustration;
                                    } else {
                                        $item['illustration'] = $product->illustration;
                                    }
                                }
                            }
                        }
                        // Convertir en euros si nécessaire (les prix viennent généralement en centimes)
                        if ($price > 1000) {
                            $price = $price / 100;
                        }
                        
                        $total += ($price * $quantity);
                    }
                }
                
                $updateData['total'] = $total;
                break;
            case 5:
                $userInfo = $stepData['userInfo'] ?? $stepData['user_info'] ?? null;
                if ($userInfo) {
                    // S'assurer que c'est bien un tableau
                    if (!is_array($userInfo)) {
                        $userInfo = json_decode($userInfo, true) ?? [];
                    }
                    $updateData['user_info'] = $userInfo;
                }
                break;
            case 6:
                $updateData['delivery_address'] = $stepData['deliveryAddress'] ?? $stepData['delivery_address'] ?? null;
                break;
            case 7:
                $updateData['payment_method'] = $stepData['paymentMethod'] ?? $stepData['payment_method'] ?? null;
                break;
        }

        \Log::info('Updating guide with data', [
            'update_data' => array_keys($updateData),
            'has_user_info' => isset($updateData['user_info']),
            'has_delivery_address' => isset($updateData['delivery_address']),
            'has_payment_method' => isset($updateData['payment_method']),
            'has_selected_products' => isset($updateData['selected_products']) && !empty($updateData['selected_products'])
        ]);

        // Mettre à jour le guide
        $this->update($updateData);
        
        // Recharger le modèle
        $this->refresh();
        
        \Log::info('Guide updated successfully', [
            'guide_id' => $this->id,
            'current_step' => $this->current_step,
            'user_info_exists' => !empty($this->user_info),
            'delivery_address_exists' => !empty($this->delivery_address),
            'payment_method_exists' => !empty($this->payment_method),
            'selected_products_exists' => !empty($this->selected_products)
        ]);
    }

    // Méthode pour créer une commande à partir du guide
    public function createOrderFromGuide()
    {
        \Log::info('Creating order from guide', [
            'guide_id' => $this->id,
            'has_user_info' => !empty($this->user_info),
            'has_delivery_address' => !empty($this->delivery_address),
            'has_payment_method' => !empty($this->payment_method),
            'has_selected_products' => !empty($this->selected_products)
        ]);

        if (empty($this->user_info) || empty($this->delivery_address) || empty($this->payment_method) || empty($this->selected_products)) {
            throw new \Exception('Informations incomplètes pour créer la commande. ' . 
                'Données manquantes: ' . 
                (empty($this->user_info) ? 'user_info, ' : '') .
                (empty($this->delivery_address) ? 'delivery_address, ' : '') .
                (empty($this->payment_method) ? 'payment_method, ' : '') .
                (empty($this->selected_products) ? 'selected_products' : ''));
        }

        // Préparer les données utilisateur
        $userInfo = $this->user_info;
        if (!is_array($userInfo)) {
            $userInfo = json_decode($userInfo, true) ?? [];
        }
        
        $userId = $this->user_id;
        
        // Créer la commande
        $orderData = [
            'user_id' => $userId,
            'address' => $this->delivery_address,
            'type' => 'individual',
            'delivery_date' => now()->addDays(3)->toDateString(),
            'meal_type' => $this->determineMealType(),
            'calendar_type' => 'jour',
            'payment_method' => $this->payment_method,
            'is_paid' => false,
            'total' => $this->total,
            'notes' => $this->generateOrderNotes(),
            'order_date' => now()
        ];

        \Log::info('Creating order with data', $orderData);

        // Créer la commande dans une transaction
        return DB::transaction(function () use ($orderData) {
            $order = Order::create($orderData);

            // Ajouter les produits à la commande
            $this->addProductsToOrder($order);

            // Lier le guide à la commande
            $this->update(['order_id' => $order->id]);

            \Log::info('Order created successfully', [
                'order_id' => $order->id,
                'guide_id' => $this->id
            ]);

            return $order;
        });
    }

    private function determineMealType()
    {
        $preferences = $this->preferences ?? [];
        if (in_array('Végétarien', $preferences) || in_array('Végan', $preferences)) {
            return 'tous';
        }
        return 'tous';
    }

    private function generateOrderNotes()
    {
        $notes = "Commande créée via le Guide Personnalisé\n";
        $notes .= "Pour: " . ($this->pour_qui ?? "Non spécifié") . "\n";
        $notes .= "Pour quand: " . ($this->pour_quand ?? "Non spécifié") . "\n";
        $notes .= "Préférences: " . (is_array($this->preferences) ? implode(", ", $this->preferences) : "Aucune") . "\n";
        $notes .= "Type de panier: " . ($this->panier_type ?? "Non spécifié") . "\n";
        
        if ($this->user_info && is_array($this->user_info)) {
            $notes .= "Client: " . ($this->user_info['prenom'] ?? '') . " " . ($this->user_info['nom'] ?? '') . "\n";
            $notes .= "Email: " . ($this->user_info['email'] ?? '') . "\n";
            $notes .= "Téléphone: " . ($this->user_info['telephone'] ?? 'Non renseigné') . "\n";
        }

        return $notes;
    }

    private function addProductsToOrder(Order $order)
    {
        if (!is_array($this->selected_products)) {
            \Log::warning('No selected products array', ['guide_id' => $this->id]);
            return;
        }

        \Log::info('Adding products to order', [
            'order_id' => $order->id,
            'product_count' => count($this->selected_products)
        ]);

        foreach ($this->selected_products as $index => $item) {
            // Support pour les deux formats possibles
            $productId = $item['product']['id'] ?? $item['id'] ?? null;
            $price = $item['product']['price'] ?? $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $name = $item['product']['name'] ?? $item['name'] ?? 'Produit inconnu';
            $illustration = $item['product']['illustration'] ?? $item['illustration'] ?? null;
            $description = $item['product']['description'] ?? $item['description'] ?? null;
            
            // Convertir le prix en euros si nécessaire
            if ($price > 1000) {
                $price = $price / 100;
            }
            
            if ($productId) {
                \Log::info('Adding product to order', [
                    'index' => $index,
                    'product_id' => $productId,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'illustration' => $illustration ? 'présente' : 'absente'
                ]);
                
                try {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $price * $quantity,
                        'illustration' => $illustration // Maintenant avec valeur
                    ]);
                    
                    \Log::info('Product added successfully to order details');
                } catch (\Exception $e) {
                    \Log::error('Error creating order detail', [
                        'error' => $e->getMessage(),
                        'product_id' => $productId,
                        'illustration' => $illustration
                    ]);
                    
                    // Essayer sans illustration si nécessaire
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $price * $quantity,
                        'illustration' => $illustration ?? 'default.jpg' // Valeur par défaut
                    ]);
                }
            } else {
                \Log::warning('Product ID not found', [
                    'index' => $index,
                    'item' => $item
                ]);
            }
        }
    }

    // Nouvelle méthode pour vérifier si le guide peut être finalisé
    public function canBeFinalized()
    {
        return !empty($this->user_info) && 
               !empty($this->delivery_address) && 
               !empty($this->payment_method) && 
               !empty($this->selected_products);
    }
}