<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartureController extends Controller
{
    public function index()
    {
        $cart = session('cart.collective', []);
        $cartFull = $this->getFullCart($cart);

        $cartIndividual = session('cart.individual', []);
        $cartCollective = session('cart.collective', []);

        return view('departure.index', [
            'cart' => $cartFull,
            'cartCount' => count($cartIndividual),
            'cartCollectif' => count($cartCollective)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'departure_date' => 'required|date',
            'delivery_address' => 'required|string|max:255',
            'cart_type' => 'required|in:individual,collective'
        ]);

        $cartType = $request->cart_type;
        $cart = session("cart.{$cartType}", []);
        $cartFull = $this->getFullCart($cart);

        $items = [];
        foreach ($cartFull as $item) {
            $items[] = [
                'product' => [
                    'id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'description' => $item['product']->description,
                    'price' => $item['product']->price,
                    'illustration' => $item['product']->illustration,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        Departure::create([
            'user_id' => Auth::id(),
            'departure_date' => $request->departure_date,
            'delivery_address' => $request->delivery_address,
            'cart_type' => $cartType,
            'cart_items' => $items
        ]);

        // Vider le panier
        session()->forget("cart.{$cartType}");

        return redirect()->route('home')
            ->with('success', 'Votre départ a été préparé avec succès !');
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