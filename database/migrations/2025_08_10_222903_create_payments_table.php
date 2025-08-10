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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_nota')->unique();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade');
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->enum('jenis_pembayaran', ['tunai', 'transfer', 'giro'])->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->enum('status', ['belum_lunas', 'lunas', 'overdue'])->default('belum_lunas');
            $table->date('tanggal_jatuh_tempo');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['sales_id', 'status']);
            $table->index(['tanggal_jatuh_tempo', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
