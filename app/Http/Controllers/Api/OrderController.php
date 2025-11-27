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
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si on utilise les produits de la session ou de la requête
        $useSessionProducts = empty($request->products);
        
        if ($useSessionProducts) {
            // Méthode originale : récupérer le panier de la session
            $cart = Session::get("cart.{$request->type}", []);
            
            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Panier vide'
                ], 400);
            }

            // Calculer le total du panier
            $cartFull = $this->getFullCart($cart);
            $total = $this->calculateTotal($cartFull);
            $orderDetailsData = [];

            foreach ($cartFull as $item) {
                $orderDetailsData[] = [
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'illustration' => $item['product']->illustration,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->price,
                    'total' => $item['total']
                ];
            }
        } else {
            // Nouvelle méthode : utiliser les produits envoyés dans la requête
            $products = $request->products;
            
            if (empty($products)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun produit dans la commande'
                ], 400);
            }

            // Calculer le total à partir des produits envoyés
            $total = 0;
            $orderDetailsData = [];

            foreach ($products as $item) {
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
                }
            }
        }

        // Déterminer si la commande est payée
        $isPaid = $request->payment_method === 'cash' || $request->payment_method === 'transfer' ? false : false;

        // Créer la commande
        $order = Order::create([
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
        ]);

        // Créer les détails de commande
        foreach ($orderDetailsData as $detail) {
            OrderDetail::create(array_merge($detail, ['order_id' => $order->id]));
        }

        // Vider le panier après création de la commande (si utilisation session)
        if ($useSessionProducts) {
            Session::forget("cart.{$request->type}");
        }

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