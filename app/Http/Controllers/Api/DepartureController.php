<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class DepartureController extends Controller
{
    public function index()
    {
        $departures = Departure::where('user_id', Auth::id())
                              ->orderBy('departure_date', 'desc')
                              ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $departures
        ]);
    }

    public function show($id)
    {
        $departure = Departure::where('id', $id)
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
            'data' => $departure
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departure_date' => 'required|date',
            'delivery_address' => 'required|string|max:255',
            'cart_type' => 'required|in:individual,collective'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartType = $request->cart_type;
        $cart = Session::get("cart.{$cartType}", []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Panier vide'
            ], 400);
        }

        $items = [];
        foreach ($this->getFullCart($cart) as $item) {
            $items[] = [
                'product' => [
                    'id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'description' => $item['product']->description,
                    'price' => $item['product']->price,
                    'illustration' => $item['product']->illustration,
                ],
                'quantity' => $item['quantity'],
                'weight' => $item['weight']
            ];
        }

        $departure = Departure::create([
            'user_id' => Auth::id(),
            'departure_date' => $request->departure_date,
            'delivery_address' => $request->delivery_address,
            'cart_type' => $cartType,
            'cart_items' => $items
        ]);

        Session::forget("cart.{$cartType}");

        return response()->json([
            'success' => true,
            'message' => 'Départ planifié avec succès',
            'data' => $departure
        ], 201);
    }

    public function destroy($id)
    {
        $departure = Departure::where('id', $id)
                             ->where('user_id', Auth::id())
                             ->first();

        if (!$departure) {
            return response()->json([
                'success' => false,
                'message' => 'Départ non trouvé'
            ], 404);
        }

        $departure->delete();

        return response()->json([
            'success' => true,
            'message' => 'Départ supprimé avec succès'
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