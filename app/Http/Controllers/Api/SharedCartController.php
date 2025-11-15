<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SharedCart;
use App\Models\SharedCartItem;
use App\Models\SharedCartPayment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SharedCartController extends Controller
{
    public function show($token)
    {
        $sharedCart = SharedCart::with(['items.product', 'user'])
                               ->where('token', $token)
                               ->first();

        if (!$sharedCart) {
            return response()->json([
                'success' => false,
                'message' => 'Panier partagé non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sharedCart
        ]);
    }

    public function share(Request $request)
    {
        $cart = Session::get('cart.collective', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Panier collectif vide'
            ], 400);
        }

        // Créer le panier partagé
        $sharedCart = SharedCart::create([
            'user_id' => Auth::id(),
            'is_paid' => false
        ]);

        foreach ($this->getFullCart($cart) as $item) {
            SharedCartItem::create([
                'shared_cart_id' => $sharedCart->id,
                'product_id' => $item['product']->id,
                'name' => $item['product']->name,
                'price' => $item['product']->price,
                'quantity' => $item['quantity'],
                'remaining_quantity' => $item['quantity'],
                'weight_kg' => $item['weight'] ?? 1,
                'illustration' => $item['product']->illustration,
                'subtitle' => $item['product']->subtitle,
                'description' => $item['product']->description
            ]);
        }

        Session::forget('cart.collective');

        return response()->json([
            'success' => true,
            'message' => 'Panier partagé créé',
            'data' => [
                'shared_cart' => $sharedCart->load('items'),
                'share_link' => url("/api/shared-carts/{$sharedCart->token}"),
                'token' => $sharedCart->token
            ]
        ], 201);
    }

    public function mySharedCarts()
    {
        $sharedCarts = SharedCart::with(['items', 'user'])
                                ->where('user_id', Auth::id())
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $sharedCarts
        ]);
    }

    public function participate(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:shared_cart_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $sharedCart = SharedCart::with('items')->where('token', $token)->first();

        if (!$sharedCart) {
            return response()->json([
                'success' => false,
                'message' => 'Panier partagé non trouvé'
            ], 404);
        }

        $total = 0;
        $participatedItems = [];

        foreach ($request->items as $itemData) {
            $item = $sharedCart->items->find($itemData['id']);
            
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => "Article non trouvé dans le panier partagé"
                ], 404);
            }

            if ($item->remaining_quantity < $itemData['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Quantité indisponible pour l'article {$item->name}. Quantité restante: {$item->remaining_quantity}"
                ], 400);
            }

            // Enregistrer la participation
            SharedCartPayment::create([
                'user_id' => Auth::id(),
                'cart_item_id' => $item->id,
                'quantity' => $itemData['quantity'],
                'payment_method' => $request->payment_method
            ]);

            // Mettre à jour la quantité restante
            $item->remaining_quantity -= $itemData['quantity'];
            $item->save();

            $itemTotal = $item->price * $itemData['quantity'] * $item->weight_kg;
            $total += $itemTotal;

            $participatedItems[] = [
                'item_id' => $item->id,
                'name' => $item->name,
                'quantity' => $itemData['quantity'],
                'price' => $item->price,
                'total' => $itemTotal
            ];
        }

        // Supprimer les items complètement achetés
        $sharedCart->items()->where('remaining_quantity', '<=', 0)->delete();

        // Vérifier si le panier est complètement acheté
        $remainingItems = $sharedCart->items()->count();
        if ($remainingItems === 0) {
            $sharedCart->update(['is_paid' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Participation enregistrée avec succès',
            'data' => [
                'total' => $total,
                'payment_method' => $request->payment_method,
                'participated_items' => $participatedItems,
                'shared_cart' => $sharedCart->fresh(['items', 'user'])
            ]
        ]);
    }

    public function destroy($id)
    {
        $sharedCart = SharedCart::where('id', $id)
                               ->where('user_id', Auth::id())
                               ->first();

        if (!$sharedCart) {
            return response()->json([
                'success' => false,
                'message' => 'Panier partagé non trouvé'
            ], 404);
        }

        $sharedCart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Panier partagé supprimé avec succès'
        ]);
    }

    private function getFullCart($cart)
    {
        $cartFull = [];
        foreach ($cart as $id => $details) {
            $product = Product::find($id);
            if ($product) {
                $cartFull[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                    'weight' => $details['weight'] ?? 1
                ];
            }
        }
        return $cartFull;
    }
}