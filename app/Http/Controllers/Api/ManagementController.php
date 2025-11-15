<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Region;

class ManagementController extends Controller
{
    public function index()
    {
        $products = Product::all();
        
        $regions = Region::all();
        $regionCounts = [];
        
        foreach ($regions as $region) {
            $regionCounts[$region->name] = Product::where('region_id', $region->id)->count();
        }

        return view('management.index', [
            'nb_products' => $products->count(),
            'regionCounts' => $regionCounts
        ]);
    }
}