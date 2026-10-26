<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sampah', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode')->unique()->nullable();
            $table->string('ikon')->default('♻️');
            $table->string('kategori')->default('Anorganik');
            $table->decimal('harga_asli_kg', 12, 2)->default(0);
            $table->decimal('harga_warga_kg', 12, 2)->default(0);
            $table->integer('poin_kg')->default(1);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['aktif', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sampah');
    }
};