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
    public function index(Request $request)
    {
        $query = Product::with(['category', 'region']);

        // Filtres
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('region_id') && $request->region_id) {
            $query->where('region_id', $request->region_id);
        }

        $products = $query->latest()->paginate(20);
        $categories = Category::all();
        $regions = Region::all();

        return view('admin.products.index', compact('products', 'categories', 'regions'));
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
            // Correction : enregistrement dans public/uploads
            $request->illustration->move(public_path('uploads'), $filename);
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
                // Correction : suppression depuis public/uploads
                $oldImagePath = public_path('uploads/' . $product->illustration);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $filename = uniqid() . '.' . $request->illustration->extension();
            // Correction : enregistrement dans public/uploads
            $request->illustration->move(public_path('uploads'), $filename);
            $product->illustration = $filename;
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit modifié avec succès !');
    }

    public function destroy(Product $product)
    {
        if ($product->illustration) {
            // Correction : suppression depuis public/uploads
            $imagePath = public_path('uploads/' . $product->illustration);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé avec succès !');
    }
}