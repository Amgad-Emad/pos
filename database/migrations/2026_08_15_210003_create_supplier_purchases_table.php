<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('supplier_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_purchase_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('wholesale_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_purchase_items');
        Schema::dropIfExists('supplier_purchases');
    }
};
