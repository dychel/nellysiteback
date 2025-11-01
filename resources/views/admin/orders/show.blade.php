@extends('admin.layout')

@section('title', 'Détails Commande #' . $order->id)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Commande #{{ $order->id }}</h1>
                <p class="text-gray-600">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $order->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $order->is_paid ? 'Payée' : 'En attente de paiement' }}
                </span>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($order->total, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations client -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Informations Client</h2>
            <div class="space-y-2">
                <p><strong>Nom :</strong> {{ $order->user->first_name ?? 'N/A' }} {{ $order->user->last_name ?? '' }}</p>
                <p><strong>Email :</strong> {{ $order->user->email ?? 'N/A' }}</p>
                <p><strong>Téléphone :</strong> {{ $order->user->phone ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Adresse de livraison -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Adresse de Livraison</h2>
            <div class="space-y-2">
                <p><strong>Adresse :</strong> {{ $order->address->address ?? 'N/A' }}</p>
                <p><strong>Ville :</strong> {{ $order->address->city ?? 'N/A' }}</p>
                <p><strong>Code postal :</strong> {{ $order->address->postal_code ?? 'N/A' }}</p>
                <p><strong>Pays :</strong> {{ $order->address->country ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Statut de la commande -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Statut de la Commande</h2>
            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut de paiement</label>
                    <select name="is_paid" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1" {{ $order->is_paid ? 'selected' : '' }}>Payée</option>
                        <option value="0" {{ !$order->is_paid ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full">
                    Mettre à jour
                </button>
            </form>
        </div>
    </div>

    <!-- Détails des produits -->
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Produits commandés</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($order->orderDetails as $detail)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($detail->illustration)
                                <img src="{{ asset('storage/products/' . $detail->illustration) }}" 
                                     alt="{{ $detail->product }}" 
                                     class="w-10 h-10 object-cover rounded mr-3">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $detail->product }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($detail->price, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $detail->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format($detail->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total général</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                            {{ number_format($order->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection