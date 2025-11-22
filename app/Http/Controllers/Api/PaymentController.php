<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::with('orderDetails.product')
                     ->where('id', $request->order_id)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        if ($order->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande a déjà été payée'
            ], 400);
        }

        // Vérifier si c'est une commande avec paiement cash ou virement
        if ($order->payment_method === 'cash' || $order->payment_method === 'transfer') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande utilise un paiement ' . 
                            ($order->payment_method === 'cash' ? 'en espèces' : 'par virement') . 
                            ' et ne nécessite pas de session de paiement en ligne'
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        foreach ($order->orderDetails as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $item->price * 100, // Convertir en centimes
                    'product_data' => [
                        'name' => $item->product->name,
                        'description' => $item->product->subtitle,
                        'images' => $item->product->illustration ? 
                                  [config('app.url') . '/storage/products/' . $item->product->illustration] : []
                    ]
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Ajouter les frais de livraison si nécessaire
        $deliveryFee = 490; // 4.90€ en centimes
        $freeDeliveryThreshold = 5000; // 50€ en centimes
        
        if ($order->total < $freeDeliveryThreshold) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $deliveryFee,
                    'product_data' => [
                        'name' => 'Frais de livraison',
                        'description' => 'Livraison à domicile'
                    ]
                ],
                'quantity' => 1,
            ];
        }

        $checkoutSession = Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('api.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('api.payment.cancel'),
            'customer_email' => Auth::user()->email,
            'metadata' => [
                'order_id' => $order->id
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => $order->id
                ]
            ]
        ]);

        $order->stripe_session_id = $checkoutSession->id;
        $order->save();

        return response()->json([
            'success' => true,
            'data' => [
                'checkout_url' => $checkoutSession->url,
                'session_id' => $checkoutSession->id
            ]
        ]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID manquant'
            ], 400);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = Session::retrieve($sessionId);
            $order = Order::where('stripe_session_id', $sessionId)->first();

            if ($order && !$order->is_paid) {
                $order->is_paid = true;
                $order->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Paiement réussi',
                'data' => [
                    'order' => $order->load(['orderDetails.product']),
                    'session' => $session
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du paiement'
            ], 500);
        }
    }

    public function cancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Paiement annulé'
        ], 400);
    }

    public function verifyPayment($orderId)
    {
        $order = Order::where('id', $orderId)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        if ($order->is_paid) {
            return response()->json([
                'success' => true,
                'message' => 'Commande déjà payée',
                'data' => [
                    'is_paid' => true,
                    'order' => $order
                ]
            ]);
        }

        // Pour les commandes cash ou virement, retourner le statut actuel
        if ($order->payment_method === 'cash' || $order->payment_method === 'transfer') {
            return response()->json([
                'success' => true,
                'message' => 'Commande en attente de paiement ' . 
                            ($order->payment_method === 'cash' ? 'en espèces' : 'par virement'),
                'data' => [
                    'is_paid' => false,
                    'order' => $order,
                    'payment_method' => $order->payment_method,
                    'requires_online_payment' => false
                ]
            ]);
        }

        // Vérifier le statut avec Stripe si une session existe
        if ($order->stripe_session_id) {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            try {
                $session = Session::retrieve($order->stripe_session_id);
                
                if ($session->payment_status === 'paid') {
                    $order->is_paid = true;
                    $order->save();
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Paiement confirmé',
                        'data' => [
                            'is_paid' => true,
                            'order' => $order
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                // Continuer si erreur de récupération
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Paiement en attente',
            'data' => [
                'is_paid' => false,
                'order' => $order,
                'requires_online_payment' => true
            ]
        ]);
    }

    /**
     * Nouvelle méthode pour traiter les paiements cash et virement
     */
    public function confirmOfflinePayment(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:cash,transfer',
            'confirmation_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('id', $orderId)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        if ($order->is_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande a déjà été payée'
            ], 400);
        }

        // Vérifier que la méthode de paiement correspond
        if ($order->payment_method !== $request->payment_method) {
            return response()->json([
                'success' => false,
                'message' => 'Méthode de paiement incompatible avec la commande'
            ], 400);
        }

        // Pour les virements, on peut stocker des données supplémentaires
        if ($request->payment_method === 'transfer' && $request->confirmation_data) {
            $order->notes = $order->notes . "\n\nDonnées virement: " . 
                           json_encode($request->confirmation_data);
        }

        // Marquer comme payée (ou en attente selon votre workflow)
        // Ici on marque directement comme payée pour la démo
        $order->is_paid = true;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Paiement ' . 
                        ($request->payment_method === 'cash' ? 'en espèces' : 'par virement') . 
                        ' confirmé',
            'data' => [
                'order' => $order->load(['orderDetails.product'])
            ]
        ]);
    }

    /**
     * Méthode pour obtenir les détails de paiement d'une commande
     */
    public function getPaymentDetails($orderId)
    {
        $order = Order::where('id', $orderId)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        $paymentDetails = [
            'order_id' => $order->id,
            'total' => $order->total,
            'is_paid' => $order->is_paid,
            'payment_method' => $order->payment_method,
            'requires_online_payment' => in_array($order->payment_method, ['card', 'paypal']),
            'stripe_session_id' => $order->stripe_session_id,
            'delivery_date' => $order->delivery_date,
            'created_at' => $order->created_at
        ];

        // Ajouter des informations spécifiques selon la méthode de paiement
        if ($order->payment_method === 'transfer' && !$order->is_paid) {
            $paymentDetails['bank_transfer_info'] = [
                'bank_name' => 'Votre Banque',
                'iban' => 'FR76 1234 5678 9012 3456 7890 123',
                'bic' => 'ABCDEFGH',
                'reference' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'amount' => number_format($order->total / 100, 2, ',', ' ') . ' €',
                'due_date' => now()->addDays(3)->format('d/m/Y')
            ];
        }

        if ($order->payment_method === 'cash' && !$order->is_paid) {
            $paymentDetails['cash_payment_info'] = [
                'amount' => number_format($order->total / 100, 2, ',', ' ') . ' €',
                'delivery_date' => $order->delivery_date?->format('d/m/Y'),
                'instructions' => 'Préparez le montant exact en espèces pour le livreur'
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $paymentDetails
        ]);
    }
}