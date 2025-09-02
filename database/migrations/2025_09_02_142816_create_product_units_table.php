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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('unit_name'); // e.g., 'Karton', 'Dus', 'Pcs', 'Ball', 'Pack'
            $table->string('unit_code', 10); // e.g., 'KTN', 'DUS', 'PCS', 'BL', 'PK'
            $table->decimal('conversion_value', 10, 2)->default(1); // berapa unit dasar dalam satuan ini
            $table->decimal('price_per_unit', 15, 2); // harga per satuan ini
            $table->integer('stock_available')->default(0); // stok dalam satuan ini
            $table->integer('stock_minimum')->default(0); // minimum stok dalam satuan ini
            $table->boolean('is_base_unit')->default(false); // apakah ini satuan dasar (terkecil)
            $table->boolean('is_active')->default(true); // status aktif satuan ini
            $table->integer('sort_order')->default(0); // urutan tampil
            $table->timestamps();

            // Indexes untuk performa
            $table->index(['product_id', 'is_active']);
            $table->index(['product_id', 'is_base_unit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
