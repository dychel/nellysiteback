<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function show($type)
    {
        $cart = Session::get("cart.{$type}", []);
        $cartFull = $this->getFullCart($cart);

        $total = $this->calculateTotal($cartFull);
        $itemCount = count($cart);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartFull,
                'total' => $total,
                'item_count' => $itemCount,
                'type' => $type
            ]
        ]);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:individual,collective',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        $type = $request->type;
        $cart = Session::get("cart.{$type}", []);
        $quantity = $request->quantity ?? 1;

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'quantity' => $quantity,
                'weight' => $product->weight_kg ?? 1
            ];
        }

        Session::put("cart.{$type}", $cart);

        $cartFull = $this->getFullCart($cart);
        $total = $this->calculateTotal($cartFull);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'data' => [
                'cart_count' => count($cart),
                'cart_total' => $total,
                'items' => $cartFull
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:individual,collective',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->type;
        $cart = Session::get("cart.{$type}", []);

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé dans le panier'
            ], 404);
        }

        $cart[$id]['quantity'] = $request->quantity;
        Session::put("cart.{$type}", $cart);

        $cartFull = $this->getFullCart($cart);
        $total = $this->calculateTotal($cartFull);

        return response()->json([
            'success' => true,
            'message' => 'Quantité mise à jour',
            'data' => [
                'cart_count' => count($cart),
                'cart_total' => $total,
                'items' => $cartFull
            ]
        ]);
    }

    public function remove(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:individual,collective'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->type;
        $cart = Session::get("cart.{$type}", []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put("cart.{$type}", $cart);

            $cartFull = $this->getFullCart($cart);
            $total = $this->calculateTotal($cartFull);

            return response()->json([
                'success' => true,
                'message' => 'Produit retiré du panier',
                'data' => [
                    'cart_count' => count($cart),
                    'cart_total' => $total,
                    'items' => $cartFull
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Produit non trouvé dans le panier'
        ], 404);
    }

    public function clear($type)
    {
        Session::forget("cart.{$type}");

        return response()->json([
            'success' => true,
            'message' => 'Panier vidé avec succès'
        ]);
    }

    public function updateWeight(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:individual,collective',
            'weight' => 'required|numeric|min:0.5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->type;
        $cart = Session::get("cart.{$type}", []);

        if (!isset($cart[$id])) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé dans le panier'
            ], 404);
        }

        $cart[$id]['weight'] = $request->weight;
        Session::put("cart.{$type}", $cart);

        $cartFull = $this->getFullCart($cart);
        $total = $this->calculateTotal($cartFull);

        return response()->json([
            'success' => true,
            'message' => 'Poids mis à jour',
            'data' => [
                'cart_count' => count($cart),
                'cart_total' => $total,
                'items' => $cartFull
            ]
        ]);
    }

    private function getFullCart($cart)
    {
        $cartFull = [];
        
        foreach ($cart as $id => $details) {
            $product = Product::with(['category', 'region'])->find($id);
            if ($product) {
                $itemTotal = $product->price * $details['quantity'] * ($details['weight'] ?? 1);
                $cartFull[] = [
                    'id' => $product->id,
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'weight' => $details['weight'] ?? 1,
                    'unit_price' => $product->price,
                    'total' => $itemTotal
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