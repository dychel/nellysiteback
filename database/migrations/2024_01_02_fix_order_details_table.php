<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Supprimer l'ancienne colonne product
            $table->dropColumn('product');
            
            // Ajouter la nouvelle colonne product_id comme foreign key
            $table->foreignId('product_id')->after('order_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Supprimer la foreign key et la colonne product_id
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            
            // Recréer l'ancienne colonne product
            $table->string('product')->after('order_id');
        });
    }
};