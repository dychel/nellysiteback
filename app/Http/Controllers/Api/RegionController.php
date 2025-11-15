<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::withCount('products')->get();

        return response()->json([
            'success' => true,
            'data' => $regions
        ]);
    }

    public function show($id)
    {
        $region = Region::withCount('products')->find($id);

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Région non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $region
        ]);
    }

    public function products($id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Région non trouvée'
            ], 404);
        }

        $products = $region->products()->with(['category', 'region'])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'region' => $region,
                'products' => $products
            ]
        ]);
    }
}