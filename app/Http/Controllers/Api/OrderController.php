<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['orderDetails.product'])
                      ->where('user_id', Auth::id())
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show($id)
    {
        $order = Order::with(['orderDetails.product'])
                     ->where('id', $id)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        // LOG: Début de la commande
        Log::info('=== DÉBUT COMMANDE ===');
        Log::info('User ID:', ['user_id' => Auth::id()]);
        Log::info('Authentifié:', ['is_authenticated' => Auth::check()]);
        Log::info('Données brutes reçues:', $request->all());

        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'type' => 'required|in:individual,collective',
            'delivery_date' => 'required|date',
            'meal_type' => 'nullable|in:chaud,froid,tous',
            'calendar_type' => 'nullable|in:jour,semaine',
            'payment_method' => 'required|in:card,paypal,transfer,cash',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            // LOG: Erreurs de validation
            Log::error('❌ ERREUR VALIDATION COMMANDE');
            Log::error('Erreurs de validation:', $validator->errors()->toArray());
            Log::error('Données reçues:', $request->all());
            Log::info('=== FIN COMMANDE AVEC ERREURS ===');

            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si on utilise les produits de la session ou de la requête
        $useSessionProducts = empty($request->products);
        
        Log::info('Mode utilisation:', ['use_session_products' => $useSessionProducts]);
        
        if ($useSessionProducts) {
            // Méthode originale : récupérer le panier de la session
            $cart = Session::get("cart.{$request->type}", []);
            
            Log::info('Panier session:', ['cart' => $cart, 'type' => $request->type]);
            
            if (empty($cart)) {
                Log::error('🚨 PANIER SESSION VIDE');
                return response()->json([
                    'success' => false,
                    'message' => 'Panier vide'
                ], 400);
            }

            // Calculer le total du panier
            $cartFull = $this->getFullCart($cart);
            $total = $this->calculateTotal($cartFull);
            $orderDetailsData = [];

            Log::info('Panier complet calculé:', [
                'items_count' => count($cartFull),
                'total' => $total
            ]);

            foreach ($cartFull as $index => $item) {
                $orderDetailsData[] = [
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'illustration' => $item['product']->illustration,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->price,
                    'total' => $item['total']
                ];
                
                Log::info("Produit session {$index}:", [
                    'id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->price,
                    'total_item' => $item['total']
                ]);
            }
        } else {
            // Nouvelle méthode : utiliser les produits envoyés dans la requête
            $products = $request->products;
            
            Log::info('Produits reçus dans requête:', [
                'count' => count($products),
                'products' => $products
            ]);
            
            if (empty($products)) {
                Log::error('🚨 AUCUN PRODUIT DANS REQUÊTE');
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun produit dans la commande'
                ], 400);
            }

            // Calculer le total à partir des produits envoyés
            $total = 0;
            $orderDetailsData = [];

            foreach ($products as $index => $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $itemTotal = $product->price * $item['quantity'];
                    $total += $itemTotal;

                    $orderDetailsData[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'illustration' => $product->illustration,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'total' => $itemTotal
                    ];
                    
                    Log::info("Produit requête {$index}:", [
                        'id' => $product->id,
                        'name' => $product->name,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'total_item' => $itemTotal
                    ]);
                } else {
                    Log::error("🚨 PRODUIT NON TROUVÉ", [
                        'product_id' => $item['product_id'],
                        'index' => $index
                    ]);
                }
            }
            
            Log::info('Total calculé:', ['total' => $total]);
        }

        // Déterminer si la commande est payée
        $isPaid = $request->payment_method === 'cash' || $request->payment_method === 'transfer' ? false : false;

        Log::info('Paiement:', [
            'method' => $request->payment_method,
            'is_paid' => $isPaid
        ]);

        // Données de la commande
        $orderData = [
            'user_id' => Auth::id(),
            'address' => $request->address,
            'type' => $request->type,
            'delivery_date' => $request->delivery_date,
            'meal_type' => $request->meal_type,
            'calendar_type' => $request->calendar_type,
            'payment_method' => $request->payment_method,
            'is_paid' => $isPaid,
            'total' => $total,
            'order_date' => now(),
            'notes' => $request->notes
        ];

        Log::info('Données commande à créer:', $orderData);

        // Créer la commande
        $order = Order::create($orderData);

        Log::info('✅ COMMANDE CRÉÉE:', ['order_id' => $order->id]);

        // Créer les détails de commande
        foreach ($orderDetailsData as $index => $detail) {
            OrderDetail::create(array_merge($detail, ['order_id' => $order->id]));
            Log::info("Détail {$index} créé:", $detail);
        }

        // Vider le panier après création de la commande (si utilisation session)
        if ($useSessionProducts) {
            Session::forget("cart.{$request->type}");
            Log::info('Panier session vidé:', ['type' => $request->type]);
        }

        Log::info('🎉 COMMANDE FINALISÉE AVEC SUCCÈS');
        Log::info('=== FIN COMMANDE ===');

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $order->load(['orderDetails.product'])
        ], 201);
    }

    public function invoice($id)
    {
        $order = Order::with(['orderDetails.product'])
                     ->where('id', $id)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }

        // Générer les données de facture
        $invoiceData = [
            'order' => $order,
            'invoice_number' => 'INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => now()->format('d/m/Y'),
            'due_date' => now()->addDays(30)->format('d/m/Y')
        ];

        return response()->json([
            'success' => true,
            'data' => $invoiceData
        ]);
    }

    private function getFullCart($cart)
    {
        $cartFull = [];
        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            if ($product) {
                $total = $product->price * $details['quantity'];
                $cartFull[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'total' => $total
                ];
            }
        }
        return $cartFull;
    }

    private function calculateTotal($cart)
    {
        return array_reduce($cart, function($total, $item) {
            return $total + $item['total'];
        }, 0);
    }
}