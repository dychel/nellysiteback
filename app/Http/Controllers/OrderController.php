<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $cart = session('cart.individual', []);
        if (empty($cart)) {
            return redirect()->route('cart.individuel')->with('error', 'Votre panier est vide.');
        }

        $addresses = Address::all();
        if ($addresses->isEmpty()) {
            $addresses = collect([
                (object)['id' => 1, 'name' => 'Maison', 'address' => '123 Rue Exemple', 'postal_code' => '75001', 'city' => 'Paris', 'country' => 'France'],
                (object)['id' => 2, 'name' => 'Bureau', 'address' => '45 Avenue Test', 'postal_code' => '69002', 'city' => 'Lyon', 'country' => 'France']
            ]);
        }

        $cartFull = $this->getFullCart($cart);

        return view('order.payment_methods', compact('cartFull', 'addresses'));
    }

    public function validateOrder(Request $request)
    {
        $cart = session('cart.individual', []);
        if (empty($cart)) {
            return redirect()->route('cart.individuel')->with('error', 'Votre panier est vide.');
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        $address = Address::find($request->address_id);
        if (!$address) {
            return redirect()->route('order.index')->with('error', 'Adresse invalide.');
        }

        // Créer la commande
        $order = Order::create([
            'user_id' => Auth::id(),
            'address_id' => $address->id,
            'is_paid' => false,
            'created_at' => now()
        ]);

        // Créer les détails de commande
        foreach ($this->getFullCart($cart) as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product' => $item['product']->name,
                'illustration' => $item['product']->illustration,
                'quantity' => $item['quantity'],
                'price' => $item['product']->price,
                'total' => $item['product']->price * $item['quantity']
            ]);
        }

        // Vider le panier
        session()->forget('cart.individual');

        return redirect()->route('survey.index', ['orderId' => $order->id])
            ->with('success', 'Votre commande a été enregistrée avec succès !');
    }

    public function collectif()
    {
        $cart = session('cart.collective', []);
        if (empty($cart)) {
            return redirect()->route('cart.collectif')->with('error', 'Votre panier collectif est vide.');
        }

        $addresses = Address::all();
        $cartFull = $this->getFullCart($cart);

        return view('order.payment_methods_collectif', compact('cartFull', 'addresses'));
    }

    public function validateCollectif(Request $request)
    {
        $cart = session('cart.collective', []);
        if (empty($cart)) {
            return redirect()->route('cart.collectif')->with('error', 'Votre panier collectif est vide.');
        }

        $request->validate([
            'address_id' => 'required|exists:addresses,id'
        ]);

        $address = Address::find($request->address_id);
        if (!$address) {
            return redirect()->route('order.collectif')->with('error', 'Adresse invalide.');
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'address_id' => $address->id,
            'is_paid' => false,
            'created_at' => now()
        ]);

        foreach ($this->getFullCart($cart) as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product' => $item['product']->name,
                'illustration' => $item['product']->illustration,
                'quantity' => $item['quantity'],
                'price' => $item['product']->price,
                'total' => $item['product']->price * $item['quantity']
            ]);
        }

        session()->forget('cart.collective');

        return redirect()->route('survey.index', ['orderId' => $order->id])
            ->with('success', 'Votre commande collective a été enregistrée avec succès !');
    }

    private function getFullCart($cart)
    {
        $cartFull = [];
        foreach ($cart as $id => $details) {
            $product = \App\Models\Product::find($id);
            if ($product) {
                $cartFull[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'weight' => $details['weight'] ?? 1
                ];
            }
        }
        return $cartFull;
    }
}