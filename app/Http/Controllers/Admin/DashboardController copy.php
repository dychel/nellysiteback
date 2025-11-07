<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\SharedCart;
use App\Models\Departure;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalSharedCarts' => SharedCart::count(),
            'totalDepartures' => Departure::count(),
            'recentOrders' => Order::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return view('admin.dashboard.index', $stats);
    }
}