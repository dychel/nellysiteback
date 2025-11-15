<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'region']);

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'illustration' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'weight_kg' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->price = $request->price;
        $product->subtitle = $request->subtitle;
        $product->description = $request->description;
        $product->weight_kg = $request->weight_kg ?? 1;
        $product->category_id = $request->category_id;
        $product->region_id = $request->region_id;

        if ($request->hasFile('illustration')) {
            $filename = uniqid() . '.' . $request->illustration->extension();
            $request->illustration->storeAs('products', $filename, 'public');
            $product->illustration = $filename;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Produit créé avec succès',
            'data' => $product->load(['category', 'region'])
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'region'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'illustration' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'weight_kg' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->price = $request->price;
        $product->subtitle = $request->subtitle;
        $product->description = $request->description;
        $product->weight_kg = $request->weight_kg ?? 1;
        $product->category_id = $request->category_id;
        $product->region_id = $request->region_id;

        if ($request->hasFile('illustration')) {
            // Supprimer l'ancienne image
            if ($product->illustration) {
                Storage::disk('public')->delete('products/' . $product->illustration);
            }
            
            $filename = uniqid() . '.' . $request->illustration->extension();
            $request->illustration->storeAs('products', $filename, 'public');
            $product->illustration = $filename;
        }

        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Produit modifié avec succès',
            'data' => $product->load(['category', 'region'])
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        if ($product->illustration) {
            Storage::disk('public')->delete('products/' . $product->illustration);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produit supprimé avec succès'
        ]);
    }
}