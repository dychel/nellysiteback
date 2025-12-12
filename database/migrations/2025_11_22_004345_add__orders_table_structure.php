<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Ajouter les champs pour la commande "Commander mes repas"
            $table->enum('type', ['individual', 'collective','departure'])->default('individual')->after('user_id');
            $table->date('delivery_date')->nullable()->after('address');
            $table->enum('meal_type', ['chaud', 'froid', 'tous'])->nullable()->after('delivery_date');
            $table->enum('calendar_type', ['jour', 'semaine'])->nullable()->after('meal_type');
            $table->enum('payment_method', ['card', 'paypal', 'transfer', 'cash'])->default('card')->after('stripe_session_id');
            $table->decimal('total', 10, 2)->default(0)->after('payment_method');
            $table->text('notes')->nullable()->after('total');
            
            // Modifier created_at pour qu'il soit la date de commande
            $table->datetime('order_date')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'delivery_date', 
                'meal_type',
                'calendar_type',
                'payment_method',
                'total',
                'notes',
                'order_date'
            ]);
        });
    }
};