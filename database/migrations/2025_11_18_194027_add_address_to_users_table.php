<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne address_id
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
            
            // Ajouter la colonne address
            $table->text('address')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer la colonne address
            $table->dropColumn('address');
            
            // Recréer la colonne address_id et la clé étrangère
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
        });
    }
};