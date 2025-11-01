<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug');
            $table->string('illustration');
            $table->string('subtitle');
            $table->text('description');
            $table->integer('price');
            $table->double('weight_kg')->nullable()->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};