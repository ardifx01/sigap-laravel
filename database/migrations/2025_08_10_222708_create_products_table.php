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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kode_item')->unique();
            $table->string('nama_barang');
            $table->text('keterangan')->nullable();
            $table->enum('jenis', ['pack', 'ball', 'dus']);
            $table->decimal('harga_jual', 15, 2);
            $table->integer('stok_tersedia')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->string('foto_produk')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'stok_tersedia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
