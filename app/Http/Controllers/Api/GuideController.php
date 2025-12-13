<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GuideController extends Controller
{
    /**
     * Initialiser ou récupérer une session de guide
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $sessionId = $request->session_id ?? Str::random(32);
        $userId = auth()->check() ? auth()->id() : $request->user_id;

        // Chercher un guide existant non complété
        $guide = Guide::where(function($query) use ($sessionId, $userId) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $sessionId);
            }
        })
        ->where('completed', false)
        ->orderBy('created_at', 'desc')
        ->first();

        // Si aucun guide non complété, en créer un nouveau
        if (!$guide) {
            $guide = Guide::create([
                'session_id' => !$userId ? $sessionId : null,
                'user_id' => $userId, // Toujours enregistrer l'user_id si disponible
                'current_step' => 1,
                'completed' => false,
                'total' => 0
            ]);
        } else {
            // IMPORTANT: Mettre à jour l'user_id si l'utilisateur vient de se connecter
            if ($userId && !$guide->user_id) {
                $guide->update(['user_id' => $userId]);
                Log::info('User ID updated during guide initialization', [
                    'guide_id' => $guide->id,
                    'new_user_id' => $userId
                ]);
            }
        }

        Log::info('Guide initialized', [
            'guide_id' => $guide->id,
            'session_id' => $guide->session_id,
            'user_id' => $guide->user_id,
            'auth_user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'guide' => $guide,
                'session_id' => $guide->session_id
            ]
        ]);
    }

    /**
     * Sauvegarder une étape du guide
     */
    public function saveStep(Request $request, $step)
    {
        $request->validate([
            'session_id' => 'required_without:guide_id|string',
            'guide_id' => 'required_without:session_id|exists:guides,id'
        ]);

        $guide = $this->findGuide($request);
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide non trouvé'
            ], 404);
        }

        // IMPORTANT: Toujours vérifier et mettre à jour l'user_id
        $currentUserId = auth()->check() ? auth()->id() : null;
        if ($currentUserId && !$guide->user_id) {
            $guide->update(['user_id' => $currentUserId]);
            Log::info('User ID updated during save step', [
                'guide_id' => $guide->id,
                'new_user_id' => $currentUserId
            ]);
        }

        Log::info('Saving guide step', [
            'guide_id' => $guide->id,
            'user_id' => $guide->user_id,
            'current_user_id' => $currentUserId,
            'step' => $step,
            'request_keys' => array_keys($request->all())
        ]);

        // Sauvegarder les données de l'étape
        $guide->saveStep($step, $request->all());

        // Vérifier si l'étape 5 (user_info) est sauvegardée
        if ($step == 5) {
            Log::info('User info saved in guide', [
                'guide_id' => $guide->id,
                'user_info_exists' => !empty($guide->user_info),
                'user_info' => $guide->user_info
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'guide' => $guide->fresh(),
                'current_step' => $step
            ]
        ]);
    }

    /**
     * Récupérer l'état actuel du guide
     */
    public function getCurrentState(Request $request)
    {
        $request->validate([
            'session_id' => 'required_without:guide_id|string',
            'guide_id' => 'required_without:session_id|exists:guides,id'
        ]);

        $guide = $this->findGuide($request);
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'guide' => $guide
            ]
        ]);
    }

    /**
     * Finaliser le guide et créer la commande
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'session_id' => 'required_without:guide_id|string',
            'guide_id' => 'required_without:session_id|exists:guides,id'
        ]);

        $guide = $this->findGuide($request);
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide non trouvé'
            ], 404);
        }

        // IMPORTANT: Mettre à jour user_id si l'utilisateur est maintenant connecté
        $currentUserId = auth()->check() ? auth()->id() : null;
        if ($currentUserId && !$guide->user_id) {
            $guide->update(['user_id' => $currentUserId]);
            Log::info('User ID updated before finalization', [
                'guide_id' => $guide->id,
                'new_user_id' => $currentUserId
            ]);
        }

        // Vérifier que le guide a un user_id (peut être null pour les invités)
        Log::info('Finalizing guide - checking data', [
            'guide_id' => $guide->id,
            'user_id' => $guide->user_id,
            'current_user_id' => $currentUserId,
            'has_user_info' => !empty($guide->user_info),
            'has_delivery_address' => !empty($guide->delivery_address),
            'has_payment_method' => !empty($guide->payment_method),
            'has_selected_products' => !empty($guide->selected_products)
        ]);

        // Vérifier que toutes les informations nécessaires sont présentes
        if (!$this->validateGuideForFinalization($guide)) {
            return response()->json([
                'success' => false,
                'message' => 'Informations incomplètes pour finaliser la commande',
                'missing_data' => [
                    'user_info' => empty($guide->user_info),
                    'delivery_address' => empty($guide->delivery_address),
                    'payment_method' => empty($guide->payment_method),
                    'selected_products' => empty($guide->selected_products)
                ]
            ], 400);
        }

        try {
            // Créer la commande
            $order = $guide->createOrderFromGuide();
            
            // Marquer le guide comme complété
            $guide->markAsCompleted($order->id);

            Log::info('Guide finalized successfully', [
                'guide_id' => $guide->id,
                'user_id' => $guide->user_id,
                'order_id' => $order->id,
                'order_user_id' => $order->user_id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'guide' => $guide,
                    'order' => $order,
                    'message' => 'Commande créée avec succès'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création commande guide', [
                'guide_id' => $guide->id,
                'user_id' => $guide->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des guides
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $guides = Guide::where('user_id', $user->id)
            ->where('completed', true)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $guides
        ]);
    }

    /**
     * Trouver le guide par session_id ou guide_id
     */
    private function findGuide(Request $request)
    {
        if ($request->guide_id) {
            return Guide::find($request->guide_id);
        }

        if ($request->session_id) {
            return Guide::where('session_id', $request->session_id)
                ->where('completed', false)
                ->first();
        }

        return null;
    }

    /**
     * Vérifier si le guide peut être finalisé
     */
    private function validateGuideForFinalization(Guide $guide)
    {
        // Vérifier que toutes les données nécessaires sont présentes
        $hasAllData = !empty($guide->user_info) && 
                      !empty($guide->delivery_address) && 
                      !empty($guide->payment_method) && 
                      !empty($guide->selected_products);

        // Vérifier que user_info contient les champs requis
        if (!empty($guide->user_info)) {
            $userInfo = is_array($guide->user_info) ? $guide->user_info : json_decode($guide->user_info, true);
            if ($userInfo) {
                $hasRequiredUserInfo = !empty($userInfo['prenom']) && 
                                      !empty($userInfo['nom']) && 
                                      !empty($userInfo['email']);
                
                if (!$hasRequiredUserInfo) {
                    Log::warning('User info incomplete', [
                        'guide_id' => $guide->id,
                        'user_info' => $userInfo
                    ]);
                    return false;
                }
            }
        }

        Log::info('Guide validation result', [
            'guide_id' => $guide->id,
            'user_id' => $guide->user_id,
            'has_all_data' => $hasAllData,
            'has_user_info' => !empty($guide->user_info),
            'has_delivery_address' => !empty($guide->delivery_address),
            'has_payment_method' => !empty($guide->payment_method),
            'has_selected_products' => !empty($guide->selected_products)
        ]);

        return $hasAllData;
    }

    /**
     * Supprimer un guide
     */
    public function destroy(Request $request, $id)
    {
        $guide = Guide::find($id);
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide non trouvé'
            ], 404);
        }

        // Vérifier les permissions
        $user = auth()->user();
        if (!$user || ($guide->user_id && $guide->user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $guide->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guide supprimé avec succès'
        ]);
    }

    /**
     * Méthode utilitaire pour formater l'adresse depuis user_info
     */
    private function formatDeliveryAddress(Guide $guide)
    {
        if (!empty($guide->delivery_address)) {
            return $guide->delivery_address;
        }

        // Si pas d'adresse spécifique, utiliser l'adresse du user_info
        if (!empty($guide->user_info)) {
            $userInfo = is_array($guide->user_info) ? $guide->user_info : json_decode($guide->user_info, true);
            if (!empty($userInfo['address'])) {
                return $userInfo['address'];
            }
        }

        return null;
    }
}