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
                    'order' => $order->load(['address', 'orderDetails.product']),
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
                'order' => $order
            ]
        ]);
    }
}