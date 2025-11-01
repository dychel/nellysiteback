<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $orders = Order::with(['address', 'orderDetails'])
                      ->where('user_id', Auth::id())
                      ->where('is_paid', true)
                      ->get();

        return view('account.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['address', 'orderDetails'])
                     ->where('id', $id)
                     ->where('user_id', Auth::id())
                     ->first();

        if (!$order) {
            return redirect()->route('home');
        }

        return view('account.order', compact('order'));
    }
}