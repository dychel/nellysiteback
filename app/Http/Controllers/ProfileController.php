<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'orders_count' => $user->orders()->count(),
                    'favorites_count' => $user->favorites()->count(),
                    'addresses_count' => $user->addresses()->count()
                ]
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        Log::info('🔄 Mise à jour du profil', [
            'user_id' => $user->id,
            'données_reçues' => $request->all()
        ]);

        // VALIDATION CORRIGÉE : Utiliser les bons champs du modèle User
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string|max:1000', // Adresse peut être longue
        ]);

        if ($validator->fails()) {
            Log::error('❌ Erreur de validation du profil', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Mettre à jour uniquement les champs qui sont présents dans la requête
            $updateData = [];
            
            if ($request->has('first_name')) {
                $updateData['first_name'] = $request->first_name;
            }
            
            if ($request->has('last_name')) {
                $updateData['last_name'] = $request->last_name;
            }
            
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }
            
            if ($request->has('phone')) {
                $updateData['phone'] = $request->phone;
            }
            
            if ($request->has('gender')) {
                $updateData['gender'] = $request->gender;
            }
            
            if ($request->has('address')) {
                $updateData['address'] = $request->address;
            }

            // Mettre à jour l'utilisateur
            $user->update($updateData);

            Log::info('✅ Profil mis à jour avec succès', [
                'user_id' => $user->id,
                'champs_mis_à_jour' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => [
                    'user' => $user->fresh() // Recharger les données fraîches
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Exception lors de la mise à jour du profil', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du profil: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    }
}