<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer la clé étrangère d'abord
            $table->dropForeign(['address_id']);
            
            // Supprimer la colonne address_id
            $table->dropColumn('address_id');
            
            // Ajouter la colonne address en texte
            $table->text('address')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer la colonne address
            $table->dropColumn('address');
            
            // Recréer address_id et sa clé étrangère
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
        });
    }
};