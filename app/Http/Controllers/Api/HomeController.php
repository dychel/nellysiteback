<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Favori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'region'])->limit(6)->get();
        
        // Poids par défaut
        foreach ($products as $product) {
            if (!$product->weight_kg) {
                $product->weight_kg = 1;
            }
        }

        $favorites = [];
        if (Auth::check()) {
            $favorites = Favori::where('user_id', Auth::id())->get();
        }

        $cartIndividual = session('cart.individual', []);
        $cartCollective = session('cart.collective', []);

        return view('home.index', compact('products', 'favorites', 'cartIndividual', 'cartCollective'));
    }

    public function toggleFavori($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('home');
        }

        $favori = Favori::where('user_id', Auth::id())
                       ->where('product_id', $product->id)
                       ->first();

        if ($favori) {
            $favori->delete();
        } else {
            Favori::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'name' => $product->name,
                'illustration' => $product->illustration,
                'created_at' => now()
            ]);
        }

        return redirect()->route('home');
    }
}