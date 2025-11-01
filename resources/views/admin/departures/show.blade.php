@extends('admin.layout')

@section('title', 'Détails Départ #' . $departure->id)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Départ #{{ $departure->id }}</h1>
                <p class="text-gray-600">Créé le {{ $departure->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $departure->cart_type === 'individual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                    {{ $departure->cart_type === 'individual' ? 'Individuel' : 'Collectif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Informations Générales</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Utilisateur</label>
                    <p class="mt-1">{{ $departure->user->first_name ?? 'N/A' }} {{ $departure->user->last_name ?? '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de départ</label>
                    <p class="mt-1">{{ $departure->departure_date->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Adresse de livraison</label>
                    <p class="mt-1">{{ $departure->delivery_address }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Actions</h2>
            <form action="{{ route('admin.departures.delete', $departure) }}" method="POST" 
                  onsubmit="return confirmDelete('Supprimer définitivement ce départ ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Supprimer ce départ
                </button>
            </form>
        </div>
    </div>

    <!-- Produits du départ -->
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Produits ({{ count($departure->cart_items) }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($departure->cart_items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if(isset($item['product']['illustration']) && $item['product']['illustration'])
                                <img src="{{ asset('storage/products/' . $item['product']['illustration']) }}" 
                                     alt="{{ $item['product']['name'] }}" 
                                     class="w-10 h-10 object-cover rounded mr-3">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item['product']['name'] ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['product']['description'] ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item['product']['price'] ?? 0, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item['quantity'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection