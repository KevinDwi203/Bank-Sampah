# Catatan Perbaikan

Setelah mengganti file, jalankan di folder project:

    composer dump-autoload
    npm install && npm run build
    php artisan storage:link
    php artisan optimize:clear

Jika database masih kosong / mau reset:  php artisan migrate:fresh --seed
Login awal: admin/admin123, petugas/petugas123, nasabah/nasabah123

Penyebab 404 setelah login: resources/js/app.js masih mengecek localStorage (sisa versi Node/JWT)
lalu redirect ke teks literal '{{ route("login") }}'. Sudah dihapus. WAJIB `npm run build` ulang.

## Function & Stored Procedure
File: database/sql/function_procedure.sql  (migration: 2026_10_05_000001_create_functions_and_procedures.php)
Pasang dengan `php artisan migrate`, atau jalankan file .sql langsung di phpMyAdmin/HeidiSQL.
Function : fn_hitung_poin, fn_kode_berikutnya, fn_total_setoran_nasabah
Procedure: sp_setor_sampah, sp_tarik_saldo, sp_laporan_bulanan
