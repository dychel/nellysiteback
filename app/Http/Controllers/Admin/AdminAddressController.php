<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AdminAddressController extends Controller
{
    public function index()
    {
        $addresses = Address::withCount('orders')->orderBy('name')->get();
        return view('admin.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255'
        ]);

        Address::create($request->all());

        return redirect()->route('admin.addresses.index')
            ->with('success', 'Adresse créée avec succès !');
    }

    public function update(Request $request, Address $address)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255'
        ]);

        $address->update($request->all());

        return redirect()->route('admin.addresses.index')
            ->with('success', 'Adresse modifiée avec succès !');
    }

    public function destroy(Address $address)
    {
        if ($address->orders()->exists()) {
            return redirect()->route('admin.addresses.index')
                ->with('error', 'Impossible de supprimer : cette adresse est utilisée dans des commandes.');
        }

        $address->delete();

        return redirect()->route('admin.addresses.index')
            ->with('success', 'Adresse supprimée avec succès !');
    }
}