<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Favori;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class MaRegionController extends Controller
{
    public function extreme_nord()
    {
        return $this->showRegionProducts('Extrême Nord', 'ma_region.extreme_nord');
    }

    public function nord()
    {
        return $this->showRegionProducts('Nord', 'ma_region.nord');
    }

    public function adamaoua()
    {
        return $this->showRegionProducts('Adamaoua', 'ma_region.adamaoua');
    }

    public function centre()
    {
        return $this->showRegionProducts('Centre', 'ma_region.centre');
    }

    public function nord_ouest()
    {
        return $this->showRegionProducts('Nord-Ouest', 'ma_region.nord_ouest');
    }

    public function ouest()
    {
        return $this->showRegionProducts('Ouest', 'ma_region.ouest');
    }

    public function sud_ouest()
    {
        return $this->showRegionProducts('Sud-Ouest', 'ma_region.sud_ouest');
    }

    public function littoral()
    {
        return $this->showRegionProducts('Littoral', 'ma_region.littoral');
    }

    public function sud()
    {
        return $this->showRegionProducts('Sud', 'ma_region.sud');
    }

    public function est()
    {
        return $this->showRegionProducts('Est', 'ma_region.est');
    }

    private function showRegionProducts($regionName, $view)
    {
        $region = Region::where('name', $regionName)->first();
        
        if (!$region) {
            $products = collect();
        } else {
            $products = Product::with(['category'])
                             ->where('region_id', $region->id)
                             ->get();
            
            // Poids par défaut
            foreach ($products as $product) {
                if (!$product->weight_kg) {
                    $product->weight_kg = 1;
                }
            }
        }

        $favorites = [];
        if (Auth::check()) {
            $favorites = Favori::where('user_id', Auth::id())->get();
        }

        $cartIndividual = session('cart.individual', []);
        $cartCollective = session('cart.collective', []);

        return view($view, [
            'products' => $products,
            'region' => $regionName,
            'favorites' => $favorites,
            'cartCount' => count($cartIndividual),
            'cartCollectif' => count($cartCollective)
        ]);
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

        return redirect()->back();
    }
}