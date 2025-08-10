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
        Schema::create('backorders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('jumlah_backorder');
            $table->integer('jumlah_terpenuhi')->default(0);
            $table->enum('status', ['pending', 'partial', 'fulfilled', 'cancelled'])->default('pending');
            $table->timestamp('expected_date')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['status', 'expected_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backorders');
    }
};
