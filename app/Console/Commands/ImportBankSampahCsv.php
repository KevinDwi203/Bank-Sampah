<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImportBankSampahCsv extends Command
{
    protected $signature = 'banksampah:import-csv
        {folder : Folder berisi nasabah.csv, transaksi.csv, arus_kas.csv}
        {--replace-demo-data : Hapus data operasional lama sebelum impor; gunakan hanya setelah backup}
        {--force : Lewati konfirmasi interaktif}
        {--dry-run : Validasi file saja, tanpa menyimpan data}';

    protected $description = 'Impor data Bank Sampah dari CSV yang sudah dinormalisasi';

    private int $nasabahCount = 0;
    private int $transaksiCount = 0;
    private int $kasCount = 0;
    private int $skippedKasNoDate = 0;

    public function handle(): int
    {
        $folder = rtrim($this->argument('folder'), DIRECTORY_SEPARATOR);
        $nasabahFile = $folder . DIRECTORY_SEPARATOR . 'nasabah.csv';
        $transaksiFile = $folder . DIRECTORY_SEPARATOR . 'transaksi.csv';
        $kasFile = $folder . DIRECTORY_SEPARATOR . 'arus_kas.csv';

        if (!is_file($nasabahFile)) {
            $this->error("File wajib tidak ditemukan: {$nasabahFile}");
            return self::FAILURE;
        }

        if ($this->option('replace-demo-data') && !$this->option('dry-run') && !$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm('Ini akan menghapus data nasabah, transaksi, penarikan, dan arus kas yang ada. Backup sudah aman?')) {
                $this->warn('Dibatalkan. Tidak ada data yang diubah.');
                return self::FAILURE;
            }
        }

        try {
            $customers = $this->readCsv($nasabahFile, ['nomor_kartu', 'nama']);
            $transactions = is_file($transaksiFile)
                ? $this->readCsv($transaksiFile, ['id_sumber', 'nomor_kartu', 'tanggal', 'kode_jenis', 'berat_kg', 'harga_asli_kg', 'harga_warga_kg', 'total'])
                : [];
            $cashRows = is_file($kasFile)
                ? $this->readCsv($kasFile, ['id_sumber', 'tanggal', 'keterangan', 'masuk', 'keluar', 'kategori'])
                : [];

            $this->validateRows($customers, $transactions, $cashRows);

            if ($this->option('dry-run')) {
                $this->info('Validasi file berhasil. Tidak ada data yang disimpan.');
                $this->line('Nasabah: ' . count($customers));
                $this->line('Transaksi: ' . count($transactions));
                $this->line('Baris arus kas: ' . count($cashRows));
                return self::SUCCESS;
            }

            DB::transaction(function () use ($customers, $transactions, $cashRows) {
                if ($this->option('replace-demo-data')) {
                    // Hanya data operasional. User, jenis sampah, dan setting tidak dihapus.
                    foreach (['penarikan', 'transaksi', 'arus_kas', 'nasabah'] as $table) {
                        if (DB::getSchemaBuilder()->hasTable($table)) {
                            DB::table($table)->delete();
                        }
                    }
                }

                $this->importCustomers($customers);
                $this->importTransactions($transactions);
                $this->importCash($cashRows);

                // Perbarui status keaktifan nasabah berdasarkan transaksi yang baru diimpor
                \App\Models\Nasabah::with('transaksiSelesai')->get()->each->updateStatusOtomatis();
            }, 3);

            $this->newLine();
            $this->info('Impor selesai.');
            $this->line("Nasabah baru: {$this->nasabahCount}");
            $this->line("Transaksi baru: {$this->transaksiCount}");
            $this->line("Arus kas baru: {$this->kasCount}");
            if ($this->skippedKasNoDate > 0) {
                $this->warn("Baris arus kas dilewati karena tanggal kosong/tidak tersedia: {$this->skippedKasNoDate}. Periksa manual; tanggal tidak ditebak.");
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Impor gagal: ' . $e->getMessage());
            $this->line('Transaksi database dibatalkan jika terjadi error di dalam proses penyimpanan.');
            return self::FAILURE;
        }
    }

    private function readCsv(string $path, array $requiredHeaders): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new RuntimeException("Tidak bisa membuka {$path}");
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new RuntimeException("CSV kosong: {$path}");
        }

        $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $headers);
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers, true)) {
                fclose($handle);
                throw new RuntimeException("Header '{$required}' tidak ada di {$path}");
            }
        }

        $rows = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count($values) === 1 && trim((string) $values[0]) === '') continue;
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw new RuntimeException("Jumlah kolom tidak cocok pada {$path}, baris {$line}");
            }
            $row = array_combine($headers, array_map(fn ($v) => trim((string) $v), $values));
            $row['_line'] = $line;
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    private function validateRows(array $customers, array $transactions, array $cashRows): void
    {
        $cards = [];
        foreach ($customers as $row) {
            $card = $this->card($row['nomor_kartu'] ?? '');
            if ($card === '' || trim($row['nama'] ?? '') === '') {
                throw new RuntimeException("nomor_kartu dan nama wajib pada nasabah.csv baris {$row['_line']}");
            }
            if (isset($cards[$card])) {
                throw new RuntimeException("Nomor kartu ganda '{$card}' di nasabah.csv");
            }
            $cards[$card] = true;
        }

        foreach ($transactions as $row) {
            $card = $this->card($row['nomor_kartu'] ?? '');
            if ($card === '' || trim($row['id_sumber'] ?? '') === '') {
                throw new RuntimeException("id_sumber dan nomor_kartu wajib pada transaksi.csv baris {$row['_line']}");
            }
            $this->date($row['tanggal'] ?? '', "transaksi.csv baris {$row['_line']}");
            foreach (['berat_kg', 'harga_asli_kg', 'harga_warga_kg', 'total'] as $field) {
                $this->number($row[$field] ?? '', "{$field}, transaksi.csv baris {$row['_line']}");
            }
        }

        foreach ($cashRows as $row) {
            if (trim($row['id_sumber'] ?? '') === '' || trim($row['keterangan'] ?? '') === '') {
                throw new RuntimeException("id_sumber dan keterangan wajib pada arus_kas.csv baris {$row['_line']}");
            }
            foreach (['masuk', 'keluar'] as $field) {
                $this->number($row[$field] ?? '', "{$field}, arus_kas.csv baris {$row['_line']}");
            }
        }
    }

    private function importCustomers(array $rows): void
    {
        foreach ($rows as $row) {
            $card = $this->card($row['nomor_kartu']);
            $existing = DB::table('nasabah')->where('nomor_kartu', $card)->first();
            if ($existing) continue;

            $code = 'NK.' . str_pad(preg_replace('/\D/', '', $card), 3, '0', STR_PAD_LEFT);
            if (DB::table('nasabah')->where('kode', $code)->exists()) {
                $code .= '.' . strtoupper(substr(sha1($card), 0, 5));
            }

            DB::table('nasabah')->insert([
                'kode' => $code,
                'nomor_kartu' => $card,
                'nama' => trim($row['nama']),
                'telepon' => $this->optional($row['telepon'] ?? null),
                'alamat' => $this->optional($row['alamat'] ?? null),
                'saldo' => $this->optional($row['saldo_awal'] ?? null) ?? 0,
                'poin' => $this->optional($row['poin_awal'] ?? null) ?? 0,
                'status' => in_array(($row['status'] ?? ''), ['Aktif', 'Tidak Aktif'], true) ? $row['status'] : 'Aktif',
                'last_deposit_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->nasabahCount++;
        }
    }

    private function importTransactions(array $rows): void
    {
        foreach ($rows as $row) {
            $card = $this->card($row['nomor_kartu']);
            $nasabah = DB::table('nasabah')->where('nomor_kartu', $card)->first();
            if (!$nasabah) throw new RuntimeException("Nomor kartu '{$card}' belum ada di nasabah.csv (transaksi baris {$row['_line']})");

            $jenis = DB::table('jenis_sampah')->where('kode', trim($row['kode_jenis']))->first();
            if (!$jenis) throw new RuntimeException("Kode jenis sampah '{$row['kode_jenis']}' tidak ditemukan di tabel jenis_sampah (baris {$row['_line']})");

            $sourceId = trim($row['id_sumber']);
            $kode = 'IMP-' . strtoupper(substr(hash('sha256', $sourceId), 0, 20));
            if (DB::table('transaksi')->where('kode', $kode)->exists()) continue;

            $berat = $this->number($row['berat_kg'], 'berat_kg');
            $hargaAsli = $this->number($row['harga_asli_kg'], 'harga_asli_kg');
            $hargaWarga = $this->number($row['harga_warga_kg'], 'harga_warga_kg');
            $total = $this->number($row['total'], 'total');
            $poin = $this->optional($row['poin_dapat'] ?? null);

            DB::table('transaksi')->insert([
                'kode' => $kode,
                'nasabah_id' => $nasabah->id,
                'jenis_id' => $jenis->id,
                'berat_kg' => $berat,
                'harga_asli_kg' => $hargaAsli,
                'harga_warga_kg' => $hargaWarga,
                'total' => $total,
                'poin_dapat' => $poin === null ? 0 : (int) $poin,
                'catatan' => $this->optional($row['catatan'] ?? null) ?? ($poin === null ? 'Impor historis; poin historis tidak tersedia di sumber.' : null),
                'status' => in_array(($row['status'] ?? 'Selesai'), ['Pending', 'Selesai', 'Dibatalkan'], true) ? ($row['status'] ?? 'Selesai') : 'Selesai',
                'tanggal' => $this->date($row['tanggal'], 'transaksi.csv'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->transaksiCount++;
        }
    }

    private function importCash(array $rows): void
    {
        $allowed = ['pengeluaran_operasional', 'pembayaran_pengurus', 'pembelian_peralatan', 'biaya_transport', 'biaya_konsumsi', 'lainnya'];
        foreach ($rows as $row) {
            if (trim($row['tanggal'] ?? '') === '') {
                $this->skippedKasNoDate++;
                continue;
            }
            $sourceId = trim($row['id_sumber']);
            $marker = '[IMPOR:' . $sourceId . ']';
            if (DB::table('arus_kas')->where('catatan', 'like', '%' . $marker . '%')->exists()) continue;

            $kategori = trim($row['kategori'] ?? 'lainnya');
            if (!in_array($kategori, $allowed, true)) $kategori = 'lainnya';
            $masuk = $this->number($row['masuk'], 'masuk');
            $keluar = $this->number($row['keluar'], 'keluar');
            $catatan = trim($row['catatan'] ?? '');
            $catatan = trim($catatan . ' ' . $marker);

            DB::table('arus_kas')->insert([
                'tanggal' => $this->date($row['tanggal'], 'arus_kas.csv'),
                'keterangan' => trim($row['keterangan']),
                'kategori' => $kategori,
                'masuk' => $masuk,
                'keluar' => $keluar,
                'saldo' => $this->optional($row['saldo'] ?? null) ?? 0,
                'catatan' => $catatan,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->kasCount++;
        }
    }

    private function card(string $value): string
    {
        $value = trim($value);
        if ($value !== '' && ctype_digit($value)) return str_pad($value, 2, '0', STR_PAD_LEFT);
        return $value;
    }

    private function date(string $value, string $context): string
    {
        $value = trim($value);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("Tanggal harus berformat YYYY-MM-DD ({$context}): '{$value}'");
        }
        return $value;
    }

    private function number(string $value, string $context): float
    {
        $value = trim(str_replace(['Rp', 'rp', ' '], '', $value));
        if ($value === '' || !is_numeric($value)) {
            throw new RuntimeException("Angka tidak valid untuk {$context}: '{$value}'. Gunakan angka polos, misalnya 2.5 atau 1500");
        }
        return (float) $value;
    }

    private function optional(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
