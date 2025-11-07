@extends('admin.layout')

@section('title', 'Tableau de Bord')

@section('content')
<div class="space-y-6">
    <!-- En-tête avec statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Produits -->
        <a href="?filter=products" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Produits</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalProducts }}</h3>
                </div>
            </div>
        </a>

        <!-- Total Commandes -->
        <a href="?filter=orders" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Commandes</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalOrders }}</h3>
                </div>
            </div>
        </a>

        <!-- Paniers Collectifs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Paniers Collectifs</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalSharedCarts }}</h3>
                </div>
            </div>
        </div>

        <!-- Départs Préparés -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <i class="fas fa-truck text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Départs Préparés</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalDepartures }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Première ligne : Trafic et Commandes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cartes de gauche : Trafic -->
        <div class="space-y-6">
            <!-- Visiteurs -->
            <a href="?filter=visitors" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Visiteurs Uniques/Jour</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $uniqueVisitorsToday }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Utilisateurs Actifs -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i class="fas fa-desktop text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Utilisateurs Actifs</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $topPlatformVisits }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de droite : Commandes -->
        <div class="space-y-6">
            <!-- Commandes Validées -->
            <a href="?filter=orders&type=validated" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Commandes Validées</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $validatedOrders }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Commandes en Attente -->
            <a href="?filter=orders&type=pending" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Commandes en Attente</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $pendingOrders }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Deuxième ligne : Utilisateurs et Démographie -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cartes de gauche : Utilisateurs -->
        <div class="space-y-6">
            <!-- Clients Hommes -->
            <a href="?filter=users&type=male" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-male text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Clients Hommes</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $maleUsers }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Clients Femmes -->
            <a href="?filter=users&type=female" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-pink-100 rounded-lg">
                            <i class="fas fa-female text-pink-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Clients Femmes</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $femaleUsers }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cartes de droite : Marketing -->
        <div class="space-y-6">
            <!-- Enquêtes -->
            <a href="?filter=surveys" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-lg">
                            <i class="fas fa-poll text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Enquêtes & Sondages</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $surveysCount }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Campagnes -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-pink-100 rounded-lg">
                            <i class="fas fa-bullhorn text-pink-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Campagnes & Pub</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $campaignsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Troisième ligne : Produits et Support -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Cartes de gauche : Produits -->
        <div class="space-y-6">
            <!-- Produits Populaires -->
            <a href="?filter=products&type=top" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-chart-line text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Produits Populaires</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $topProductsCount }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Produits Moins Vendus -->
            <a href="?filter=products&type=low" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <i class="fas fa-chart-bar text-orange-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Produits Moins Vendus</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $lowProductsCount }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cartes de droite : Support et Régions -->
        <div class="space-y-6">
            <!-- Support -->
            <a href="?filter=support" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-headset text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Demandes S.A.V</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $supportTickets }}</h3>
                        </div>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </a>

            <!-- Régions -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Meilleure Région -->
                <a href="?filter=regions&type=top" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 bg-teal-100 rounded-lg">
                                <i class="fas fa-map-marker-alt text-teal-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Meilleure Région</p>
                                <h3 class="text-2xl font-bold text-gray-800">{{ $topRegionSales }}</h3>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Région Moins Active -->
                <a href="?filter=regions&type=low" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="p-3 bg-gray-100 rounded-lg">
                                <i class="fas fa-map text-gray-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600">Région Moins Active</p>
                                <h3 class="text-2xl font-bold text-gray-800">{{ $lowRegionSales }}</h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Section principale des données (remplace les dernières commandes quand un filtre est actif) -->
@if($currentFilter)
<div class="bg-white rounded-lg shadow mt-6">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">
            @switch($currentFilter)
                @case('visitors') 
                    Visiteurs du Jour
                    @break
                @case('orders') 
                    @if($currentType === 'validated')
                        Commandes Validées
                    @elseif($currentType === 'pending')
                        Commandes en Attente
                    @else
                        Toutes les Commandes
                    @endif
                    @break
                @case('surveys') 
                    Enquêtes et Sondages
                    @break
                @case('users')
                    @if($currentType === 'male')
                        Clients Hommes
                    @elseif($currentType === 'female')
                        Clients Femmes
                    @else
                        Tous les Utilisateurs
                    @endif
                    @break
                @case('support') 
                    Demandes S.A.V
                    @break
                @case('products')
                    @if($currentType === 'top')
                        Produits les Plus Vendus
                    @elseif($currentType === 'low')
                        Produits les Moins Vendus
                    @else
                        Tous les Produits
                    @endif
                    @break
                @case('regions')
                    @if($currentType === 'top')
                        Meilleures Régions
                    @elseif($currentType === 'low')
                        Régions Moins Actives
                    @else
                        Toutes les Régions
                    @endif
                    @break
            @endswitch
            <span class="text-sm text-gray-500 ml-2">({{ $filteredData->count() }} résultats)</span>
        </h3>
        <a href="{{ route('admin.dashboard') }}" class="text-sm bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition-colors">
            Fermer
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    @switch($currentFilter)
                        @case('visitors')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière connexion</th>
                            @break
                        @case('orders')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            @break
                        @case('surveys')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q3</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q4</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q5</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q6</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            @break
                        @case('users')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Genre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'inscription</th>
                            @break
                        @case('support')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priorité</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            @break
                        @case('products')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ventes</th>
                            @break
                        @case('regions')
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Région</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commandes</th>
                            @break
                    @endswitch
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($filteredData as $item)
                <tr class="hover:bg-gray-50">
                    @switch($currentFilter)
                        @case('visitors')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->first_name }} {{ $item->last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->last_login_at ? $item->last_login_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            @break
                        @case('orders')
                            <td class="px-6 py-4 whitespace-nowrap">#{{ $item->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->user->first_name ?? 'N/A' }} {{ $item->user->last_name ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $total = $item->orderDetails->sum('total');
                                @endphp
                                {{ number_format($total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $item->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $item->is_paid ? 'Payée' : 'En attente' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            @break
                        @case('surveys')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->user->first_name ?? 'N/A' }} {{ $item->user->last_name ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q1 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q2 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q3 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q4 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q5 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">{{ $item->q6 }}/5</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->created_at->format('d/m/Y') }}</td>
                            @break
                        @case('users')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->first_name }} {{ $item->last_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $item->gender === 'male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ $item->gender === 'male' ? 'Homme' : 'Femme' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->created_at->format('d/m/Y') }}</td>
                            @break
                        @case('support')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->subject }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->user->first_name ?? 'N/A' }} {{ $item->user->last_name ?? '' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $item->status === 'resolved' ? 'bg-green-100 text-green-800' : 
                                       ($item->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $item->status === 'resolved' ? 'Résolu' : 
                                       ($item->status === 'in_progress' ? 'En cours' : 'Ouvert') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $item->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                       ($item->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    {{ $item->priority === 'high' ? 'Haute' : 
                                       ($item->priority === 'medium' ? 'Moyenne' : 'Basse') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            @break
                        @case('products')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($item->price, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->sales_count }} ventes</td>
                            @break
                        @case('regions')
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item->orders_count }} commandes</td>
                            @break
                    @endswitch
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">Aucune donnée trouvée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<!-- Dernières commandes (affiché seulement quand aucun filtre n'est actif) -->
<div class="bg-white rounded-lg shadow mt-6">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Dernières Commandes</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentOrders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $order->user->first_name ?? 'N/A' }} {{ $order->user->last_name ?? '' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $total = $order->orderDetails->sum('total');
                        @endphp
                        {{ number_format($total, 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full {{ $order->is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $order->is_paid ? 'Payée' : 'En attente' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Aucune commande récente</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection