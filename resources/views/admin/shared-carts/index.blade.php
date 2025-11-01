@extends('admin.layout')

@section('title', 'Paniers Collectifs')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Paniers Collectifs</h1>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.shared-carts.index') }}" method="GET" class="flex space-x-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Payés</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>En cours</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tableau des paniers collectifs -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Créateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($sharedCarts as $cart)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                        {{ Str::limit($cart->token, 8) }}...
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $cart->user->first_name }} {{ $cart->user->last_name }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $cart->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                            {{ $cart->items->count() }} produit(s)
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $cart->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $cart->is_paid ? 'Payé' : 'En cours' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $cart->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.shared-carts.show', $cart) }}" 
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye mr-1"></i>Détails
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Aucun panier collectif trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($sharedCarts->hasPages())
    <div class="px-6 py-4 border-t bg-gray-50">
        {{ $sharedCarts->links() }}
    </div>
    @endif
</div>
@endsection