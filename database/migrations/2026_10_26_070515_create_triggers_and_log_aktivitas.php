<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = file_get_contents(database_path('sql/trigger.sql'));

        $sql = preg_replace('/^DELIMITER.*$/mi', '', $sql);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        foreach (explode('$$', $sql) as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                try {
                    DB::unprepared($statement);
                } catch (\Throwable $e) {
                    logger()->warning("Trigger/statement skipped: " . $e->getMessage());
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['tr_transaksi_bi_validasi', 'tr_transaksi_ai_log', 'tr_transaksi_au_status', 'tr_transaksi_ad_log'] as $t) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$t}");
        }
        DB::unprepared('DROP TABLE IF EXISTS log_aktivitas');
    }
};