<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favori::with('product.category', 'product.region')
                          ->where('user_id', Auth::id())
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    public function toggle($productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        $favorite = Favori::where('user_id', Auth::id())
                         ->where('product_id', $productId)
                         ->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorite = false;
            $message = 'Produit retiré des favoris';
        } else {
            Favori::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'name' => $product->name,
                'illustration' => $product->illustration,
            ]);
            $isFavorite = true;
            $message = 'Produit ajouté aux favoris';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_favorite' => $isFavorite,
                'product_id' => $productId
            ]
        ]);
    }

    public function remove($productId)
    {
        $favorite = Favori::where('user_id', Auth::id())
                         ->where('product_id', $productId)
                         ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé dans les favoris'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré des favoris'
        ]);
    }
}