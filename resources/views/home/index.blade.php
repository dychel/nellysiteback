<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nelly Ecommerce - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-2xl font-bold text-gray-800">Nelly Ecommerce</div>
                <div class="space-x-4">
                    <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900">Produits</a>
                    <a href="{{ route('cart.individuel') }}" class="text-gray-600 hover:text-gray-900">Panier</a>
                    @auth
                        <a href="{{ route('account') }}" class="text-gray-600 hover:text-gray-900">Mon Compte</a>
                        @if(Auth::user()->is_admin)
                            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">Admin</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Connexion</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Bienvenue sur Nelly Ecommerce</h1>
            <p class="text-xl text-gray-600">Votre boutique en ligne préférée</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($products as $product)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                @if($product->illustration)
                    <img src="{{ asset('storage/products/' . $product->illustration) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500">Image non disponible</span>
                    </div>
                @endif
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $product->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $product->subtitle }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-green-600">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                        <a href="{{ route('products.show', $product->slug) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Voir le produit
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($products->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">Aucun produit disponible pour le moment.</p>
            @auth
                @if(Auth::user()->is_admin)
                    <a href="{{ route('admin.products.create') }}" class="mt-4 inline-block bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700">
                        Ajouter un produit
                    </a>
                @endif
            @endauth
        </div>
        @endif
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>&copy; 2024 Nelly Ecommerce. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>