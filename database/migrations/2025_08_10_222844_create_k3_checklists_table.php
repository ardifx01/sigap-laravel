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
        Schema::create('k3_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->boolean('cek_ban')->default(false);
            $table->boolean('cek_oli')->default(false);
            $table->boolean('cek_air_radiator')->default(false);
            $table->boolean('cek_rem')->default(false);
            $table->boolean('cek_bbm')->default(false);
            $table->boolean('cek_terpal')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['delivery_id']);
            $table->index(['driver_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('k3_checklists');
    }
};
