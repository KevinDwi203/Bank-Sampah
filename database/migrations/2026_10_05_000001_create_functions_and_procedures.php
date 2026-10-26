<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Membuat function & stored procedure dari file database/sql/function_procedure.sql
     * (DELIMITER tidak dibutuhkan lewat PDO, jadi file dipecah per blok CREATE).
     */
    public function up(): void
    {
        $sql = file_get_contents(database_path('sql/function_procedure.sql'));

        // buang baris DELIMITER dan komentar, lalu pecah berdasarkan penutup "$$"
        $sql = preg_replace('/^DELIMITER.*$/mi', '', $sql);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        foreach (explode('$$', $sql) as $statement) {
            foreach (preg_split('/;\s*(?=DROP|CREATE|$)/i', trim($statement)) as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                // DROP ... ; yang berdiri sendiri sudah terpisah, CREATE berakhir di END
                DB::unprepared($part);
            }
        }
    }

    public function down(): void
    {
        foreach (['fn_hitung_poin', 'fn_kode_berikutnya', 'fn_total_setoran_nasabah'] as $f) {
            DB::unprepared("DROP FUNCTION IF EXISTS {$f}");
        }
        foreach (['sp_setor_sampah', 'sp_tarik_saldo', 'sp_laporan_bulanan'] as $p) {
            DB::unprepared("DROP PROCEDURE IF EXISTS {$p}");
        }
    }
};
