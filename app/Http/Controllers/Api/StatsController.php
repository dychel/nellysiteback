<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Region;

class StatsController extends Controller
{
    public function index()
    {
        return view('stats.index');
    }

    public function dia_bat()
    {
        $regions = Region::withCount('products')->get();
        return view('stats.bat', compact('regions'));
    }

    public function camembert()
    {
        $regions = Region::withCount('products')->get();
        return view('stats.cam', compact('regions'));
    }
}