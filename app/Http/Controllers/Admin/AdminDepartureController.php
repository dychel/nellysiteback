<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use Illuminate\Http\Request;

class AdminDepartureController extends Controller
{
    public function index(Request $request)
    {
        $query = Departure::with('user');

        if ($request->has('cart_type') && $request->cart_type) {
            $query->where('cart_type', $request->cart_type);
        }

        $departures = $query->latest()->paginate(20);

        return view('admin.departures.index', compact('departures'));
    }

    public function show(Departure $departure)
    {
        $departure->load('user');
        return view('admin.departures.show', compact('departure'));
    }

    public function destroy(Departure $departure)
    {
        $departure->delete();

        return redirect()->route('admin.departures.index')
            ->with('success', 'Départ supprimé avec succès !');
    }
}