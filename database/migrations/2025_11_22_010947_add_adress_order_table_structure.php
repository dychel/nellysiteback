<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer l'ancienne clé étrangère address_id si elle existe
            if (Schema::hasColumn('orders', 'address_id')) {
                $table->dropForeign(['address_id']);
                $table->dropColumn('address_id');
            }
            
            // Ajouter le nouveau champ address
            if (!Schema::hasColumn('orders', 'address')) {
                $table->string('address')->after('user_id');
            }
            
            // Ajouter les autres champs nécessaires
            if (!Schema::hasColumn('orders', 'type')) {
                $table->enum('type', ['individual', 'collective'])->default('individual')->after('address');
            }
            
            if (!Schema::hasColumn('orders', 'delivery_date')) {
                $table->date('delivery_date')->after('type');
            }
            
            if (!Schema::hasColumn('orders', 'meal_type')) {
                $table->enum('meal_type', ['chaud', 'froid', 'tous'])->nullable()->after('delivery_date');
            }
            
            if (!Schema::hasColumn('orders', 'calendar_type')) {
                $table->enum('calendar_type', ['jour', 'semaine'])->nullable()->after('meal_type');
            }
            
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->enum('payment_method', ['card', 'paypal', 'transfer', 'cash'])->default('card')->after('calendar_type');
            }
            
            if (!Schema::hasColumn('orders', 'total')) {
                $table->decimal('total', 10, 2)->default(0)->after('stripe_session_id');
            }
            
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('total');
            }
            
            if (!Schema::hasColumn('orders', 'order_date')) {
                $table->datetime('order_date')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Supprimer les nouveaux champs
            $table->dropColumn([
                'address',
                'type',
                'delivery_date', 
                'meal_type',
                'calendar_type',
                'payment_method',
                'total',
                'notes',
                'order_date'
            ]);
            
            // Recréer l'ancienne structure si nécessaire
            // $table->foreignId('address_id')->constrained()->onDelete('cascade');
        });
    }
};