<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class AdminRegionController extends Controller
{
    public function index()
    {
        $regions = Region::withCount('products')->orderBy('name')->get();
        return view('admin.regions.index', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name'
        ]);

        Region::create($request->only('name'));

        return redirect()->route('admin.regions.index')
            ->with('success', 'Région créée avec succès !');
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id
        ]);

        $region->update($request->only('name'));

        return redirect()->route('admin.regions.index')
            ->with('success', 'Région modifiée avec succès !');
    }

    public function destroy(Region $region)
    {
        if ($region->products()->exists()) {
            return redirect()->route('admin.regions.index')
                ->with('error', 'Impossible de supprimer : cette région contient des produits.');
        }

        $region->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', 'Région supprimée avec succès !');
    }
}