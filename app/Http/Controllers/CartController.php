<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function individuel()
    {
        $products = Product::with(['category', 'region'])->limit(20)->get();
        
        // Poids par défaut
        foreach ($products as $product) {
            if (!$product->weight_kg) {
                $product->weight_kg = 1;
            }
        }

        $cart = Session::get('cart.individual', []);
        $cartFull = $this->getFullCart($cart);

        $cartCount = count($cart);
        $cartCollective = Session::get('cart.collective', []);
        $cartCollectifCount = count($cartCollective);

        return view('cart.individuel', compact('cartFull', 'products', 'cartCount', 'cartCollectifCount'));
    }

    public function collectif()
    {
        $products = Product::with(['category', 'region'])->limit(20)->get();
        
        foreach ($products as $product) {
            if (!$product->weight_kg) {
                $product->weight_kg = 1;
            }
        }

        $cart = Session::get('cart.collective', []);
        $cartFull = $this->getFullCart($cart);

        $cartCollectifCount = count($cart);
        $cartIndividual = Session::get('cart.individual', []);
        $cartCount = count($cartIndividual);

        return view('cart.collectif', compact('cartFull', 'products', 'cartCount', 'cartCollectifCount'));
    }

    public function add($id, $type = 'individual')
    {
        $product = Product::find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Produit non trouvé.');
        }

        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'quantity' => 1,
                'weight' => $product->weight_kg ?? 1
            ];
        }

        Session::put("cart.{$type}", $cart);

        return redirect()->route($type === 'collective' ? 'cart.collectif' : 'cart.individuel')
            ->with('success', 'Produit ajouté au panier');
    }

    public function delete($id, $type = 'individual')
    {
        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put("cart.{$type}", $cart);
            
            return redirect()->back()->with('success', 'Produit retiré du panier');
        }

        return redirect()->back()->with('error', 'Produit non trouvé dans le panier');
    }

    public function increase($id, $type = 'individual')
    {
        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
            Session::put("cart.{$type}", $cart);
        }

        return redirect()->back();
    }

    public function decrease($id, $type = 'individual')
    {
        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id])) {
            if ($cart[$id]['quantity'] > 1) {
                $cart[$id]['quantity']--;
            } else {
                unset($cart[$id]);
            }
            Session::put("cart.{$type}", $cart);
        }

        return redirect()->back();
    }

    public function increaseWeight($id, $type = 'individual')
    {
        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id])) {
            $cart[$id]['weight'] += 0.5;
            Session::put("cart.{$type}", $cart);
        }

        return redirect()->back();
    }

    public function decreaseWeight($id, $type = 'individual')
    {
        $cart = Session::get("cart.{$type}", []);
        
        if (isset($cart[$id]) && $cart[$id]['weight'] > 0.5) {
            $cart[$id]['weight'] -= 0.5;
            Session::put("cart.{$type}", $cart);
        }

        return redirect()->back();
    }

    private function getFullCart($cart)
    {
        $cartFull = [];
        
        foreach ($cart as $id => $details) {
            $product = Product::with(['category', 'region'])->find($id);
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