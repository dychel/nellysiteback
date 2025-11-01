<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('illustration')->nullable();
            // SUPPRIMEZ cette ligne : $table->datetime('created_at');
            $table->timestamps(); // Cela crée created_at et updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};