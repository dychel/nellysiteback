@extends('admin.layout')

@section('title', 'Départs Préparés')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Départs Préparés</h1>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.departures.index') }}" method="GET" class="flex space-x-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de panier</label>
            <select name="cart_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les types</option>
                <option value="individual" {{ request('cart_type') === 'individual' ? 'selected' : '' }}>Individuel</option>
                <option value="collective" {{ request('cart_type') === 'collective' ? 'selected' : '' }}>Collectif</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tableau des départs -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date de départ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date création</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($departures as $departure)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $departure->user->first_name ?? 'N/A' }} {{ $departure->user->last_name ?? '' }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $departure->user->email ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $departure->departure_date->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ Str::limit($departure->delivery_address, 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $departure->cart_type === 'individual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $departure->cart_type === 'individual' ? 'Individuel' : 'Collectif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                            {{ count($departure->cart_items) }} produit(s)
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $departure->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.departures.show', $departure) }}" 
                               class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.departures.delete', $departure) }}" 
                                  method="POST" 
                                  onsubmit="return confirmDelete('Supprimer ce départ ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Aucun départ préparé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($departures->hasPages())
    <div class="px-6 py-4 border-t bg-gray-50">
        {{ $departures->links() }}
    </div>
    @endif
</div>
@endsection