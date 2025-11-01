<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Favori;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'region']);

        // Recherche par nom
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filtre par catégorie
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filtre par région
        if ($request->has('region') && $request->region) {
            $query->where('region_id', $request->region);
        }

        $products = $query->get();

        // Poids par défaut
        foreach ($products as $product) {
            if (!$product->weight_kg) {
                $product->weight_kg = 1;
            }
        }

        $favorites = [];
        $favorisCount = 0;

        if (Auth::check()) {
            $favorites = Favori::where('user_id', Auth::id())->get();
            $favorisCount = Favori::where('user_id', Auth::id())->count();
        }

        $categories = Category::all();
        $regions = Region::all();

        return view('products.index', compact('products', 'favorites', 'favorisCount', 'categories', 'regions'));
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'region'])
                         ->where('slug', $slug)
                         ->first();

        if (!$product) {
            return redirect()->route('products.index');
        }

        if (!$product->weight_kg) {
            $product->weight_kg = 1;
        }

        $isFavorite = false;
        if (Auth::check()) {
            $isFavorite = Favori::where('user_id', Auth::id())
                               ->where('product_id', $product->id)
                               ->exists();
        }

        return view('products.show', compact('product', 'isFavorite'));
    }

    public function toggleFavori($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $product = Product::find($id);
        if (!$product) {
            return redirect()->route('products.index');
        }

        $favori = Favori::where('user_id', Auth::id())
                        ->where('product_id', $product->id)
                        ->first();

        if ($favori) {
            $favori->delete();
            $message = 'Produit retiré des favoris';
        } else {
            Favori::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'name' => $product->name,
                'illustration' => $product->illustration,
                'created_at' => now()
            ]);
            $message = 'Produit ajouté aux favoris';
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    public function favoris()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $favoris = Favori::with('product')->where('user_id', Auth::id())->get();

        return view('favoris.index', compact('favoris'));
    }
}