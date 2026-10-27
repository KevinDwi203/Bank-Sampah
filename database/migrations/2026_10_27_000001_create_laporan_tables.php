<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('laporan')) {
            Schema::create('laporan', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->date('tanggal');
                $table->string('keterangan', 255)->default('Laporan Sampah');
                $table->decimal('total_berat_kg', 10, 3)->default(0);
                $table->decimal('total_nilai', 15, 2)->default(0);
                $table->text('catatan')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_dummy')->default(false)->index();
                $table->timestamps();

                $table->index(['tanggal', 'is_dummy']);
            });
        }

        if (!Schema::hasTable('laporan_items')) {
            Schema::create('laporan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
                $table->foreignId('jenis_id')->nullable()->constrained('jenis_sampah')->nullOnDelete();
                $table->string('nama_jenis', 100);
                $table->decimal('berat_kg', 10, 3)->default(0);
                $table->decimal('harga_kg', 12, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_items');
        Schema::dropIfExists('laporan');
    }
};
