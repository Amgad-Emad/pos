<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->date('date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('sale_amount', 12, 2)->default(0);
            $table->decimal('total_after_sale', 12, 2);
            $table->decimal('paid_amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->string('payment_method');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->decimal('price', 12, 2);
            $table->decimal('price_after_sale', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
