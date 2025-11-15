<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'region'])->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $regions = Region::all();
        return view('admin.products.create', compact('categories', 'regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'illustration' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'weight_kg' => 'nullable|numeric|min:0'
        ]);

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

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit créé avec succès !');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $regions = Region::all();
        return view('admin.products.edit', compact('product', 'categories', 'regions'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'region_id' => 'nullable|exists:regions,id',
            'illustration' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'weight_kg' => 'nullable|numeric|min:0'
        ]);

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

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit modifié avec succès !');
    }

    public function destroy(Product $product)
    {
        if ($product->illustration) {
            Storage::disk('public')->delete('products/' . $product->illustration);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès !');
    }
}