<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminAddressController extends Controller
{
    public function index()
    {
        // Récupérer les adresses sans withCount (puisque la relation directe n'existe plus)
        $addresses = Address::orderBy('name')->get();
        
        // Ajouter manuellement le comptage des commandes
        foreach ($addresses as $address) {
            $address->orders_count = Order::where('address', $address->address)->count();
        }

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
        // Vérifier si l'adresse est utilisée dans des commandes
        $orderCount = Order::where('address', $address->address)->count();
        
        if ($orderCount > 0) {
            return redirect()->route('admin.addresses.index')
                ->with('error', 'Impossible de supprimer : cette adresse est utilisée dans ' . $orderCount . ' commande(s).');
        }

        $address->delete();

        return redirect()->route('admin.addresses.index')
            ->with('success', 'Adresse supprimée avec succès !');
    }
}