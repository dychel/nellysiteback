<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedCart;
use Illuminate\Http\Request;

class AdminSharedCartController extends Controller
{
    public function index(Request $request)
    {
        $query = SharedCart::with(['user', 'items']);

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_paid', $request->status);
        }

        $sharedCarts = $query->latest()->paginate(20);

        return view('admin.shared-carts.index', compact('sharedCarts'));
    }

    public function show(SharedCart $sharedCart)
    {
        $sharedCart->load(['user', 'items.product', 'payments.user']);
        return view('admin.shared-carts.show', compact('sharedCart'));
    }
}