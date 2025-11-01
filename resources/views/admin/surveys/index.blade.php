@extends('admin.layout')

@section('title', 'Enquêtes de Satisfaction')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Enquêtes de Satisfaction</h1>
    <a href="{{ route('admin.surveys.stats') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
        <i class="fas fa-chart-bar mr-2"></i>Statistiques
    </a>
</div>

<!-- Statistiques rapides -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <p class="text-sm text-gray-600">Total Enquêtes</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $stats['totalSurveys'] }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <p class="text-sm text-gray-600">Moyenne Générale</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ number_format($stats['totalAverage'], 1) }}/5</h3>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <p class="text-sm text-gray-600">Satisfaction Produits</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ number_format($stats['averageQ1'], 1) }}/5</h3>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <p class="text-sm text-gray-600">Satisfaction Service</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ number_format($stats['averageQ2'], 1) }}/5</h3>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form action="{{ route('admin.surveys.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 w-full">
                <i class="fas fa-search mr-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Tableau des enquêtes -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q1</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q2</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q3</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q4</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q5</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Q6</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Moyenne</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($surveys as $survey)
                @php
                    $average = ($survey->q1 + $survey->q2 + $survey->q3 + $survey->q4 + $survey->q5 + $survey->q6) / 6;
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $survey->user->first_name }} {{ $survey->user->last_name }}
                        </div>
                        <div class="text-sm text-gray-500">{{ $survey->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q1 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q1 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q1 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q2 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q2 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q2 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q3 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q3 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q3 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q4 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q4 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q4 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q5 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q5 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q5 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                        <span class="inline-block w-6 h-6 rounded-full {{ $survey->q6 >= 4 ? 'bg-green-100 text-green-800' : ($survey->q6 >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $survey->q6 }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                        <span class="inline-block px-2 py-1 rounded-full {{ $average >= 4 ? 'bg-green-100 text-green-800' : ($average >= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ number_format($average, 1) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $survey->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.surveys.show', $survey) }}" 
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye mr-1"></i>Détails
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                        Aucune enquête trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($surveys->hasPages())
    <div class="px-6 py-4 border-t bg-gray-50">
        {{ $surveys->links() }}
    </div>
    @endif
</div>
@endsection