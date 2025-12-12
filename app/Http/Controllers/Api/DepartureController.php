<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartureController extends Controller
{
    /**
     * Display a listing of the departures.
     */
    public function index(Request $request)
    {
        try {
            $query = Departure::with(['user', 'order'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc');

            // Filtrage par statut si fourni
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Recherche si fournie
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('delivery_address', 'like', '%' . $request->search . '%');
                });
            }

            $departures = $query->paginate($request->per_page ?? 10);

            return response()->json([
                'success' => true,
                'message' => 'Liste des départs récupérée avec succès',
                'data' => $departures
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération départs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des départs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified departure.
     */
    public function show($id)
    {
        try {
            $departure = Departure::with(['user', 'order', 'order.orderDetails.product'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$departure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Départ non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Départ récupéré avec succès',
                'data' => $departure
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération départ: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du départ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created departure.
     */
    public function store(Request $request)
    {
        // LOG: Voir les données reçues
        \Log::info('=== DÉBUT Création départ ===');
        \Log::info('User ID: ' . Auth::id());
        \Log::info('Données reçues:', $request->all());
        
        // Validation des données
        $validator = Validator::make($request->all(), [
            'departure_date' => 'required|date',
            'delivery_address' => 'required|string|max:500',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:card,paypal,transfer,cash',
            'notes' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Calcul du total et préparation des items
            $total = 0;
            $cartItems = [];

            \Log::info('Processing products...');
            foreach ($request->products as $item) {
                $product = Product::find($item['id']);
                
                if (!$product) {
                    \Log::error('Product not found: ' . $item['id']);
                    throw new \Exception("Produit non trouvé: " . $item['id']);
                }

                \Log::info('Product found:', [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'illustration' => $product->illustration
                ]);

                // Convertir le prix de centimes en euros
                $priceInEuros = $product->price / 100;
                $itemTotal = $priceInEuros * $item['quantity'];
                $total += $itemTotal;

                // Structure COMPLÈTE des items pour cart_items
                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description ?? '',
                    'price' => $priceInEuros, // Stocker en euros
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                    'illustration' => $product->illustration ?? 'default.jpg', // TOUJOURS fournir une valeur
                    'weight_kg' => $product->weight_kg ?? 0
                ];
            }

            \Log::info('Cart items prepared:', $cartItems);
            \Log::info('Total calculated: ' . $total);

            // Création de la commande - CORRECTION : utiliser 'individual' au lieu de 'departure'
            \Log::info('Creating order...');
            $order = Order::create([
                'user_id' => Auth::id(),
                'address' => $request->delivery_address,
                'type' => 'individual', // CORRECTION : 'individual' existe dans l'ENUM
                'delivery_date' => $request->departure_date,
                'payment_method' => $request->payment_method,
                'is_paid' => $request->payment_method === 'cash' ? false : true,
                'total' => $total,
                'notes' => $request->notes ?? '',
                'order_date' => now()
            ]);

            \Log::info('Order created with ID: ' . $order->id);

            // Création des détails de commande - CORRECTION : inclure 'illustration'
            \Log::info('Creating order details...');
            foreach ($cartItems as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'illustration' => $item['illustration'], // CHAMP OBLIGATOIRE
                    'quantity' => $item['quantity'],
                    'price' => $item['price'], // Note: dans OrderDetail c'est 'price', pas 'unit_price'
                    'total' => $item['total']
                ]);
                \Log::info('Order detail created for product: ' . $item['product_id']);
            }

            // Création du départ
            \Log::info('Creating departure...');
            $departureData = [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'departure_date' => $request->departure_date,
                'delivery_address' => $request->delivery_address,
                'cart_type' => 'individual',
                'cart_items' => $cartItems,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes ?? '',
                'total' => $total,
                'status' => 'confirmed',
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone
            ];

            \Log::info('Departure data to create:', $departureData);
            
            $departure = Departure::create($departureData);

            \Log::info('Departure created with ID: ' . $departure->id);

            DB::commit();

            // Charger les relations pour la réponse
            $departure->load(['user', 'order']);

            \Log::info('=== SUCCÈS Création départ ===');

            return response()->json([
                'success' => true,
                'message' => 'Départ planifié avec succès',
                'data' => $departure
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('=== ERREUR Création départ ===');
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            \Log::error('=== FIN ERREUR ===');
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du départ: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified departure.
     */
    public function update(Request $request, $id)
    {
        try {
            $departure = Departure::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$departure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Départ non trouvé'
                ], 404);
            }

            // Validation pour la mise à jour
            $validator = Validator::make($request->all(), [
                'departure_date' => 'sometimes|date',
                'delivery_address' => 'sometimes|string|max:500',
                'first_name' => 'sometimes|string|max:100',
                'last_name' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|max:150',
                'phone' => 'sometimes|string|max:20',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|in:pending,confirmed,preparing,delivered,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mise à jour du départ
            $departure->update($request->only([
                'departure_date', 'delivery_address', 'first_name', 
                'last_name', 'email', 'phone', 'notes', 'status'
            ]));

            // Mise à jour de la commande associée si elle existe
            if ($departure->order_id && ($request->has('departure_date') || $request->has('delivery_address') || $request->has('notes'))) {
                $orderUpdateData = [];
                if ($request->has('departure_date')) {
                    $orderUpdateData['delivery_date'] = $request->departure_date;
                }
                if ($request->has('delivery_address')) {
                    $orderUpdateData['address'] = $request->delivery_address;
                }
                if ($request->has('notes')) {
                    $orderUpdateData['notes'] = $request->notes;
                }
                
                if (!empty($orderUpdateData)) {
                    Order::where('id', $departure->order_id)->update($orderUpdateData);
                }
            }

            // Recharger les relations
            $departure->load(['user', 'order']);

            return response()->json([
                'success' => true,
                'message' => 'Départ mis à jour avec succès',
                'data' => $departure
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour départ: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du départ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified departure.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $departure = Departure::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$departure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Départ non trouvé'
                ], 404);
            }

            // Supprimer la commande associée si elle existe
            if ($departure->order_id) {
                $order = Order::find($departure->order_id);
                if ($order) {
                    // Supprimer les détails de commande
                    OrderDetail::where('order_id', $order->id)->delete();
                    // Supprimer la commande
                    $order->delete();
                }
            }

            // Supprimer le départ
            $departure->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Départ supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur suppression départ: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du départ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update departure status.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $departure = Departure::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$departure) {
                return response()->json([
                    'success' => false,
                    'message' => 'Départ non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,confirmed,preparing,delivered,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $departure->update(['status' => $request->status]);

            // Recharger les relations
            $departure->load(['user', 'order']);

            return response()->json([
                'success' => true,
                'message' => 'Statut du départ mis à jour avec succès',
                'data' => $departure
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour statut départ: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departure statistics for the authenticated user.
     */
    public function getStats()
    {
        try {
            $stats = [
                'total' => Departure::where('user_id', Auth::id())->count(),
                'pending' => Departure::where('user_id', Auth::id())->where('status', 'pending')->count(),
                'confirmed' => Departure::where('user_id', Auth::id())->where('status', 'confirmed')->count(),
                'preparing' => Departure::where('user_id', Auth::id())->where('status', 'preparing')->count(),
                'delivered' => Departure::where('user_id', Auth::id())->where('status', 'delivered')->count(),
                'cancelled' => Departure::where('user_id', Auth::id())->where('status', 'cancelled')->count(),
                'total_amount' => Departure::where('user_id', Auth::id())->sum('total')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur statistiques départ: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test endpoint for debugging.
     */
    public function test(Request $request)
    {
        \Log::info('Test endpoint called by user: ' . Auth::id());
        
        try {
            // Test avec un produit existant
            $product = Product::first();
            
            if (!$product) {
                throw new \Exception('Aucun produit trouvé dans la base de données');
            }

            // Créer un départ de test complet
            $order = Order::create([
                'user_id' => Auth::id(),
                'address' => 'Adresse de test',
                'type' => 'individual',
                'delivery_date' => '2024-12-25',
                'payment_method' => 'card',
                'is_paid' => true,
                'total' => 100.00,
                'notes' => 'Test note',
                'order_date' => now()
            ]);

            // Créer un détail de commande
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'illustration' => $product->illustration ?? 'default.jpg',
                'quantity' => 1,
                'price' => 100.00,
                'total' => 100.00
            ]);

            // Créer un départ
            $departure = Departure::create([
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'departure_date' => '2024-12-25',
                'delivery_address' => 'Adresse de test',
                'cart_type' => 'individual',
                'cart_items' => json_encode([[
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 100.00,
                    'quantity' => 1,
                    'total' => 100.00,
                    'illustration' => $product->illustration ?? 'default.jpg'
                ]]),
                'payment_method' => 'card',
                'total' => 100.00,
                'status' => 'pending',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone' => '0123456789'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test réussi',
                'departure_id' => $departure->id,
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'data' => $departure
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Test error: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de test: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Get recent departures.
     */
    public function getRecent()
    {
        try {
            $departures = Departure::with(['user', 'order'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Départs récents récupérés avec succès',
                'data' => $departures
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération départs récents: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des départs récents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search departures.
     */
    public function search(Request $request)
    {
        try {
            $query = Departure::with(['user', 'order'])
                ->where('user_id', Auth::id());

            if ($request->has('query') && !empty($request->query)) {
                $searchTerm = $request->query;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('delivery_address', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && !empty($request->start_date)) {
                $query->where('departure_date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && !empty($request->end_date)) {
                $query->where('departure_date', '<=', $request->end_date);
            }

            $departures = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);

            return response()->json([
                'success' => true,
                'message' => 'Recherche de départs effectuée avec succès',
                'data' => $departures
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur recherche départs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche des départs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}