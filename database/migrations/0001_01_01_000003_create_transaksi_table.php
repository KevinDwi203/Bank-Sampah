<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('nasabah_id')->constrained('nasabah')->cascadeOnDelete();
            $table->foreignId('jenis_id')->nullable()->constrained('jenis_sampah')->nullOnDelete();
            $table->decimal('berat_kg', 10, 3);
            $table->decimal('harga_asli_kg', 12, 2);
            $table->decimal('harga_warga_kg', 12, 2);
            $table->decimal('total', 15, 2);
            $table->integer('poin_dapat')->default(0);
            $table->text('catatan')->nullable();
            $table->enum('status', ['Pending', 'Selesai', 'Dibatalkan'])->default('Selesai');
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['nasabah_id', 'tanggal', 'status']);
            $table->index(['jenis_id', 'tanggal']);
            $table->index(['tanggal', 'status']);
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};