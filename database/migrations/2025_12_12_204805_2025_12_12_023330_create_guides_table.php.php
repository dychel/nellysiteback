<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // Pour les utilisateurs non connectés
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('pour_qui')->nullable();
            $table->string('pour_quand')->nullable();
            $table->json('preferences')->nullable(); // Stocker les préférences en JSON
            $table->string('panier_type')->nullable();
            $table->json('selected_products')->nullable(); // Stocker les produits sélectionnés en JSON
            $table->decimal('total', 10, 2)->default(0);
            $table->string('delivery_address')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('user_info')->nullable(); // Stocker les infos utilisateur en JSON
            $table->integer('current_step')->default(1);
            $table->boolean('completed')->default(false);
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->index('session_id');
            $table->index(['user_id', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};