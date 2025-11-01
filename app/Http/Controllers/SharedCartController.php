<?php

namespace App\Http\Controllers;

use App\Models\SharedCart;
use App\Models\SharedCartItem;
use App\Models\SharedCartPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharedCartController extends Controller
{
    public function share()
    {
        $cart = session('cart.collective', []);
        if (empty($cart)) {
            return redirect()->route('cart.collectif')->with('warning', 'Votre panier collectif est vide !');
        }

        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Vous devez être connecté pour partager un panier.');
        }

        // Créer le panier partagé
        $sharedCart = SharedCart::create([
            'user_id' => Auth::id(),
            'is_paid' => false
        ]);

        foreach ($this->getFullCart($cart) as $item) {
            SharedCartItem::create([
                'shared_cart_id' => $sharedCart->id,
                'product_id' => $item['product']->id,
                'name' => $item['product']->name,
                'price' => $item['product']->price,
                'quantity' => $item['quantity'],
                'remaining_quantity' => $item['quantity'],
                'weight_kg' => $item['weight'] ?? 1,
                'illustration' => $item['product']->illustration,
                'subtitle' => $item['product']->subtitle,
                'description' => $item['product']->description
            ]);
        }

        $link = route('shared-cart.view', ['token' => $sharedCart->token]);

        $cartIndividual = session('cart.individual', []);
        $cartCollective = session('cart.collective', []);

        return view('cart.share', [
            'link' => $link,
            'sharedCart' => $sharedCart,
            'cartCount' => count($cartIndividual),
            'cartCollectif' => count($cartCollective),
            'cartCollectifAll' => $this->getFullCart($cartCollective)
        ]);
    }

    public function viewSharedCart($token)
    {
        $sharedCart = SharedCart::with('items.product')->where('token', $token)->first();

        if (!$sharedCart) {
            return redirect()->route('products.index')->with('danger', 'Ce panier partagé est introuvable.');
        }

        return view('cart.shared_cart', [
            'sharedCart' => $sharedCart,
            'cartCollectifAll' => $sharedCart->items
        ]);
    }

    public function paymentCollectif(Request $request)
    {
        $token = $request->token;
        $sharedCart = SharedCart::with('items')->where('token', $token)->first();

        if (!$sharedCart) {
            return redirect()->route('products.index')->with('danger', 'Panier partagé introuvable.');
        }

        $selectedIds = $request->products ?? [];
        $cartItems = $sharedCart->items->whereIn('id', $selectedIds);

        if ($cartItems->isEmpty()) {
            return redirect()->route('shared-cart.view', ['token' => $token])
                ->with('error', 'Aucun produit sélectionné pour le paiement.');
        }

        $total = $cartItems->sum('total');

        return view('order.payment_methods_collectif', [
            'cartCollectifAll' => $cartItems,
            'cartCollectif' => $cartItems->count(),
            'total' => $total,
            'token' => $token,
        ]);
    }

    public function validateCollectif(Request $request)
    {
        $token = $request->token;
        $sharedCart = SharedCart::with('items')->where('token', $token)->first();

        if (!$sharedCart) {
            return redirect()->route('products.index')->with('danger', 'Panier partagé introuvable.');
        }

        $paymentMethod = $request->payment_method ?? 'cash';
        $selectedIds = $request->products ?? [];

        $cartItems = $sharedCart->items->whereIn('id', $selectedIds);

        if ($cartItems->isEmpty()) {
            return redirect()->route('home')->with('success', 'Effectué avec succès !');
        }

        $total = $cartItems->sum('total');

        // Enregistrer les paiements
        foreach ($cartItems as $item) {
            SharedCartPayment::create([
                'user_id' => Auth::id(),
                'cart_item_id' => $item->id,
                'quantity' => $item->quantity,
                'payment_method' => $paymentMethod
            ]);

            // Mettre à jour la quantité restante
            $item->remaining_quantity -= $item->quantity;
            $item->save();
        }

        // Supprimer les items complètement achetés
        $cartItems->where('remaining_quantity', '<=', 0)->each->delete();

        return redirect()->route('cart.collectif')
            ->with('success', "Commande validée avec $paymentMethod. Montant : " . number_format($total/100, 0, ',', ' ') . " FCFA");
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