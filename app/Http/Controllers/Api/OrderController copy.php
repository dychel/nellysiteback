<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['address', 'orderDetails.product'])
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
        $order = Order::with(['address', 'orderDetails.product'])
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
            'address_id' => 'required|exists:addresses,id',
            'type' => 'required|in:individual,collective'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que l'adresse appartient à l'utilisateur
        $address = Address::where('id', $request->address_id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse non trouvée'
            ], 404);
        }

        $cart = Session::get("cart.{$request->type}", []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Panier vide'
            ], 400);
        }

        // Calculer le total
        $cartFull = $this->getFullCart($cart);
        $total = $this->calculateTotal($cartFull);

        // Créer la commande
        $order = Order::create([
            'user_id' => Auth::id(),
            'address_id' => $address->id,
            'is_paid' => false,
            'total' => $total,
            'created_at' => now()
        ]);

        // Créer les détails de commande
        foreach ($cartFull as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item['product']->id,
                'illustration' => $item['product']->illustration,
                'quantity' => $item['quantity'],
                'price' => $item['product']->price,
                'total' => $item['total']
            ]);
        }

        // Vider le panier
        Session::forget("cart.{$request->type}");

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'data' => $order->load(['address', 'orderDetails.product'])
        ], 201);
    }

    public function invoice($id)
    {
        $order = Order::with(['address', 'orderDetails.product'])
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
                $total = $product->price * $details['quantity'] * ($details['weight'] ?? 1);
                $cartFull[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'weight' => $details['weight'] ?? 1,
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