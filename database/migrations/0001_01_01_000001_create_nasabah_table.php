<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nasabah', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->integer('poin')->default(0);
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->date('last_deposit_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_deposit_at']);
            $table->index('kode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabah');
    }
};