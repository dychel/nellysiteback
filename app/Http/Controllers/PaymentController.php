<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function index($orderId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $order = Order::with('orderDetails')
                     ->where('id', $orderId)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Commande non trouvée.');
        }

        $products_for_stripe = [];
        foreach ($order->orderDetails as $product) {
            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->price * 100,
                    'product_data' => [
                        'name' => $product->product,
                        'images' => [
                            config('app.url') . '/storage/products/' . $product->illustration
                        ]
                    ]
                ],
                'quantity' => $product->quantity,
            ];
        }

        $checkout_session = Session::create([
            'line_items' => $products_for_stripe,
            'mode' => 'payment',
            'success_url' => route('payment.success', ['stripe_session_id' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => route('cart.individuel'),
        ]);

        $order->stripe_session_id = $checkout_session->id;
        $order->save();

        return redirect($checkout_session->url);
    }

    public function success($stripeSessionId)
    {
        $order = Order::where('stripe_session_id', $stripeSessionId)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Commande non trouvée.');
        }

        if (!$order->is_paid) {
            $order->is_paid = true;
            $order->save();
            
            // Vider le panier
            session()->forget('cart.individual');
        }

        return view('payment.success', compact('order'));
    }
}