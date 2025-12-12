@extends('admin.layout')

@section('title', 'Détails Commande #' . $order->id)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Commande #{{ $order->order_number }}</h1>
                <p class="text-gray-600">Passée le {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                <p class="text-gray-600 mt-1">
                    <span class="font-medium">Date de livraison:</span> {{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}
                </p>
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
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Nom complet</p>
                    <p class="font-medium">{{ $order->user->first_name ?? 'N/A' }} {{ $order->user->last_name ?? '' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $order->user->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Téléphone</p>
                    <p class="font-medium">{{ $order->user->phone ?? 'Non renseigné' }}</p>
                </div>
            </div>
        </div>

        <!-- Adresse de livraison et détails -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Livraison & Détails</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Adresse de livraison</p>
                    <p class="font-medium">{{ $order->address }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type de commande</p>
                    <p class="font-medium">{{ $order->type === 'individual' ? 'Individuel' : 'Collectif' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type de repas</p>
                    <p class="font-medium">{{ $order->meal_type_formatted }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type de calendrier</p>
                    <p class="font-medium">{{ $order->calendar_type_formatted }}</p>
                </div>
            </div>
        </div>

        <!-- Statut de la commande -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Paiement</h2>
            <div class="space-y-3 mb-4">
                <div>
                    <p class="text-sm text-gray-500">Méthode de paiement</p>
                    <p class="font-medium">{{ $order->payment_method_formatted }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date de la commande</p>
                    <p class="font-medium">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            
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
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 w-full transition duration-200">
                    <i class="fas fa-save mr-2"></i>Mettre à jour le statut
                </button>
            </form>
        </div>
    </div>

    <!-- Notes de commande -->
    @if($order->notes)
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-lg font-semibold mb-3">Notes de la commande</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-700">{{ $order->notes }}</p>
        </div>
    </div>
    @endif

    <!-- Détails des produits -->
    <div class="bg-white rounded-lg shadow mt-6">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Produits commandés</h2>
            <p class="text-sm text-gray-500">{{ $order->orderDetails->count() }} produit(s)</p>
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
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($detail->illustration)
                                <div class="flex-shrink-0 w-16 h-16 mr-4">
                                    <img src="{{ asset('/uploads/' . $detail->illustration) }}" 
                                         alt="{{ $detail->product_name }}" 
                                         class="w-full h-full object-cover rounded-lg"
                                         onerror="this.src='{{ asset('images/placeholder-product.jpg') }}'">
                                </div>
                                @else
                                <div class="flex-shrink-0 w-16 h-16 mr-4 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $detail->product_name }}</div>
                                    @if($detail->product && $detail->product->description)
                                    <div class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit($detail->product->description, 80) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($detail->price, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-800 rounded-full">
                                {{ $detail->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            {{ number_format($detail->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                Sous-total: {{ number_format($order->orderDetails->sum('total'), 0, ',', ' ') }} FCFA
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            Total général
                        </td>
                        <td class="px-6 py-4 text-lg font-bold text-gray-900 border-t-2 border-gray-200">
                            {{ number_format($order->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bouton retour -->
    <div class="mt-6">
        <a href="{{ route('admin.orders.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste des commandes
        </a>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection