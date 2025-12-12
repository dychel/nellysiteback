<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departures', function (Blueprint $table) {
            // Ajout des colonnes nécessaires
            $table->string('payment_method')->nullable()->after('delivery_address');
            $table->text('notes')->nullable()->after('payment_method');
            $table->decimal('total', 10, 2)->default(0)->after('notes');
            $table->string('status')->default('pending')->after('total');
            $table->string('first_name')->nullable()->after('status');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('email')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained('orders')->onDelete('cascade');
            
            // Index pour les recherches
            $table->index('status');
            $table->index('departure_date');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('departures', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'notes',
                'total',
                'status',
                'first_name',
                'last_name',
                'email',
                'phone',
                'order_id'
            ]);
            
            $table->dropIndex(['status']);
            $table->dropIndex(['departure_date']);
            $table->dropIndex(['order_id']);
        });
    }
};