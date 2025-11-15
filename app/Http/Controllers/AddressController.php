<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    public function show($id)
    {
        $address = Address::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $address = Address::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => $request->country
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Adresse créée avec succès',
            'data' => $address
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $address->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Adresse mise à jour avec succès',
            'data' => $address
        ]);
    }

    public function destroy($id)
    {
        $address = Address::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse non trouvée'
            ], 404);
        }

        // Vérifier si l'adresse est utilisée dans des commandes
        if ($address->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cette adresse est utilisée dans des commandes et ne peut pas être supprimée'
            ], 422);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Adresse supprimée avec succès'
        ]);
    }
}