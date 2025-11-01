<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Nelly Ecommerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-6">
                <h1 class="text-2xl font-bold">Nelly Admin</h1>
                <p class="text-gray-300 text-sm">Panel d'administration</p>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>Tableau de bord
                </a>
                <a href="{{ route('admin.products.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.products.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-box mr-3"></i>Produits
                </a>
                <a href="{{ route('admin.categories.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-tags mr-3"></i>Catégories
                </a>
                <a href="{{ route('admin.regions.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.regions.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-map-marker-alt mr-3"></i>Régions
                </a>
                <a href="{{ route('admin.addresses.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.addresses.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-address-book mr-3"></i>Adresses
                </a>
                <a href="{{ route('admin.orders.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.orders.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-shopping-cart mr-3"></i>Commandes
                </a>
                <a href="{{ route('admin.shared-carts.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.shared-carts.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-3"></i>Paniers Collectifs
                </a>
                <a href="{{ route('admin.departures.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.departures.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-truck mr-3"></i>Départs Préparés
                </a>
                <a href="{{ route('admin.users.index') }}" class="block py-3 px-6 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-3"></i>Utilisateurs
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="flex justify-between items-center px-6 py-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">@yield('title', 'Administration')</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">Bonjour, {{ Auth::user()->first_name }}</span>
                        <a href="https://nos-provisions.netlify.app/" class="text-gray-600 hover:text-gray-800" target="_blank">
                            <i class="fas fa-external-link-alt mr-1"></i>Voir le site
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Scripts pour l'interactivité
        function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
            return confirm(message);
        }
    </script>
</body>
</html>