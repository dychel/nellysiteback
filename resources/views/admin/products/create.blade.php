@extends('admin.layout')

@section('title', 'Nouveau Produit')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Ajouter un nouveau produit</h1>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations de base -->
                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">Informations de base</h2>
                </div>

                <!-- Nom du produit -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom du produit *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sous-titre -->
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Sous-titre *</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('subtitle')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prix -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Prix (FCFA) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Poids -->
                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-gray-700 mb-2">Poids (kg)</label>
                    <input type="number" name="weight_kg" id="weight_kg" value="{{ old('weight_kg', 1) }}" min="0" step="0.1"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('weight_kg')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Catégorie *</label>
                    <select name="category_id" id="category_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        <option value="">Sélectionnez une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Région -->
                <div>
                    <label for="region_id" class="block text-sm font-medium text-gray-700 mb-2">Région</label>
                    <select name="region_id" id="region_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Sélectionnez une région</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                                {{ $region->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image du produit -->
                <div class="md:col-span-2">
                    <label for="illustration" class="block text-sm font-medium text-gray-700 mb-2">Image du produit *</label>
                    <input type="file" name="illustration" id="illustration" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           required>
                    @error('illustration')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Formats acceptés: JPEG, PNG, JPG, GIF. Taille max: 2MB</p>
                    <p class="text-sm text-gray-500">L'image sera enregistrée dans: public/uploads/</p>
                </div>

                <!-- Aperçu de l'image -->
                <div class="md:col-span-2">
                    <div id="image-preview" class="mt-2 hidden">
                        <p class="text-sm text-gray-700 mb-2">Aperçu:</p>
                        <img id="preview" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" id="description" rows="6"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition duration-200">
                    Annuler
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                    <i class="fas fa-save mr-2"></i>Créer le produit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Aperçu de l'image
    document.getElementById('illustration').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('image-preview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.classList.add('hidden');
        }
    });

    // Validation du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
        const price = document.getElementById('price').value;
        if (price < 0) {
            e.preventDefault();
            alert('Le prix ne peut pas être négatif.');
        }
    });
</script>
@endsection