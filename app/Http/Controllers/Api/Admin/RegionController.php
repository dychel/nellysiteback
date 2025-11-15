<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:regions,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $region = Region::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Région créée avec succès',
            'data' => $region
        ], 201);
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

    public function update(Request $request, $id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Région non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $region->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Région modifiée avec succès',
            'data' => $region
        ]);
    }

    public function destroy($id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json([
                'success' => false,
                'message' => 'Région non trouvée'
            ], 404);
        }

        if ($region->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une région contenant des produits'
            ], 422);
        }

        $region->delete();

        return response()->json([
            'success' => true,
            'message' => 'Région supprimée avec succès'
        ]);
    }
}