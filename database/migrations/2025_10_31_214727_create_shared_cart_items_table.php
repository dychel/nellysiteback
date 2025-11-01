<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_cart_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('remaining_quantity');
            $table->string('illustration')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->double('weight_kg')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_cart_items');
    }
};