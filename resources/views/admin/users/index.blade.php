@extends('admin.layout')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, téléphone..." 
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
            <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous les rôles</option>
                <option value="1" {{ request('role') === '1' ? 'selected' : '' }}>Administrateurs</option>
                <option value="0" {{ request('role') === '0' ? 'selected' : '' }}>Utilisateurs</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 w-full">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tableau des utilisateurs -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commandes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Favoris</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscription</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $user->first_name }} {{ $user->last_name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $user->email ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                            {{ $user->orders_count }} commande(s)
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                            {{ $user->favorites_count }} favori(s)
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <select name="is_admin" onchange="this.form.submit()" 
                                    class="text-xs rounded-full px-2 py-1 {{ $user->is_admin ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Admin</option>
                                <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>Utilisateur</option>
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->created_at->format('d/m/Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye mr-1"></i>Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Aucun utilisateur trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="px-6 py-4 border-t bg-gray-50">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection