<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['users', 'nasabah', 'transaksi', 'penarikan', 'arus_kas'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'is_dummy')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->boolean('is_dummy')->default(false)->index();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['users', 'nasabah', 'transaksi', 'penarikan', 'arus_kas'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_dummy')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('is_dummy');
                });
            }
        }
    }
};
