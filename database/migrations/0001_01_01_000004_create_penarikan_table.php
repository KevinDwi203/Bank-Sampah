<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penarikan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->decimal('jumlah', 15, 2);
            $table->string('metode')->default('Tunai');
            $table->text('catatan')->nullable();
            $table->enum('status', ['Pending', 'Selesai', 'Dibatalkan'])->default('Pending');
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['nasabah_id', 'tanggal', 'status']);
            $table->index(['tanggal', 'status']);
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan');
    }
};