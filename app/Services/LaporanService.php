<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Penarikan;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    public function getLaporanBulanan(int $bulan, int $tahun): array
    {
        $start = "{$tahun}-" . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $end = date('Y-m-t', strtotime($start));

        $totalKg = Transaksi::selesai()->whereBetween('tanggal', [$start, $end])->sum('berat_kg');
        $totalNilai = Transaksi::selesai()->whereBetween('tanggal', [$start, $end])->sum('total');
        $totalTransaksi = Transaksi::selesai()->whereBetween('tanggal', [$start, $end])->count();
        $nasabahAktif = Nasabah::whereHas('transaksiSelesai', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal', [$start, $end]);
        })->count();

        return [
            'total_kg' => $totalKg,
            'total_nilai' => $totalNilai,
            'total_transaksi' => $totalTransaksi,
            'nasabah_aktif' => $nasabahAktif,
        ];
    }

    public function getLaporanMingguan(int $bulan, int $tahun): array
    {
        $startOfMonth = "{$tahun}-" . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        $weeks = $this->getWeeksInMonth($startOfMonth, $endOfMonth);

        $results = [];
        foreach ($weeks as $i => [$weekStart, $weekEnd]) {
            $transaksis = Transaksi::selesai()
                ->whereBetween('tanggal', [$weekStart, $weekEnd])
                ->with(['nasabah', 'jenisSampah'])
                ->get();

            $results[] = [
                'minggu' => $i + 1,
                'tanggal_mulai' => $weekStart,
                'tanggal_selesai' => $weekEnd,
                'total_kg' => $transaksis->sum('berat_kg'),
                'total_nilai' => $transaksis->sum('total'),
                'total_transaksi' => $transaksis->count(),
                'nasabah_count' => $transaksis->pluck('nasabah_id')->unique()->count(),
                'transaksi' => $transaksis,
            ];
        }

        return $results;
    }

    public function getRekapMingguan(string $dari, string $sampai): array
    {
        $transaksis = Transaksi::selesai()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->with(['nasabah', 'jenisSampah'])
            ->orderBy('tanggal')
            ->orderBy('nasabah_id')
            ->get();

        $rekap = [];
        foreach ($transaksis as $tr) {
            $key = $tr->tanggal->format('Y-m-d') . '|' . $tr->nasabah_id;
            if (!isset($rekap[$key])) {
                $rekap[$key] = [
                    'tanggal' => $tr->tanggal,
                    'nasabah' => $tr->nasabah,
                    'items' => [],
                    'total_kg' => 0,
                    'total_nilai' => 0,
                ];
            }
            $rekap[$key]['items'][] = $tr;
            $rekap[$key]['total_kg'] += $tr->berat_kg;
            $rekap[$key]['total_nilai'] += $tr->total;
        }

        return array_values($rekap);
    }

    public function getLaporanPerJenis(int $bulan, int $tahun): array
    {
        $start = "{$tahun}-" . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $end = date('Y-m-t', strtotime($start));

        return Transaksi::selesai()
            ->whereBetween('tanggal', [$start, $end])
            ->join('jenis_sampah', 'transaksi.jenis_id', '=', 'jenis_sampah.id')
            ->selectRaw('jenis_sampah.nama, jenis_sampah.ikon, jenis_sampah.kategori,
                COALESCE(SUM(transaksi.berat_kg), 0) as total_kg,
                COALESCE(SUM(transaksi.total), 0) as total_nilai,
                COUNT(transaksi.id) as jumlah_transaksi')
            ->groupBy('jenis_sampah.id', 'jenis_sampah.nama', 'jenis_sampah.ikon', 'jenis_sampah.kategori')
            ->orderByDesc('total_kg')
            ->get()
            ->toArray();
    }

    /**
     * Rekap sampah per jenis (tanpa nama nasabah) memakai HARGA ASLI.
     * Nilai dihitung per transaksi: berat x harga_asli_kg yang tersimpan saat transaksi,
     * sehingga laporan periode lama tidak berubah kalau harga master diubah.
     */
    public function getRekapPerJenisAsli(string $dari, string $sampai): array
    {
        $rows = Transaksi::selesai()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->join('jenis_sampah', 'transaksi.jenis_id', '=', 'jenis_sampah.id')
            ->selectRaw('jenis_sampah.id, jenis_sampah.nama,
                COALESCE(SUM(transaksi.berat_kg), 0) as total_kg,
                COALESCE(SUM(transaksi.berat_kg * transaksi.harga_asli_kg), 0) as total_nilai')
            ->groupBy('jenis_sampah.id', 'jenis_sampah.nama')
            ->orderBy('jenis_sampah.nama')
            ->get();

        $items = $rows->map(function ($r) {
            $kg = (float) $r->total_kg;
            $nilai = (float) $r->total_nilai;

            return [
                'nama' => $r->nama,
                'total_kg' => $kg,
                // harga rata-rata tertimbang; sama dengan harga asli bila tidak berubah dalam periode
                'harga_asli_kg' => $kg > 0 ? $nilai / $kg : 0,
                'total_nilai' => $nilai,
            ];
        })->all();

        return [
            'items' => $items,
            'total_kg' => array_sum(array_column($items, 'total_kg')),
            'total_nilai' => array_sum(array_column($items, 'total_nilai')),
        ];
    }

    public function getLaporanPerNasabah(int $bulan, int $tahun): array
    {
        $start = "{$tahun}-" . str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) . '-01';
        $end = date('Y-m-t', strtotime($start));

        $weeks = $this->getWeeksInMonth($start, $end);

        $nasabahs = Nasabah::whereHas('transaksiSelesai', function ($q) use ($start, $end) {
            $q->whereBetween('tanggal', [$start, $end]);
        })->with(['transaksiSelesai' => function ($q) use ($start, $end) {
            $q->whereBetween('tanggal', [$start, $end]);
        }])->get();

        $results = [];
        foreach ($nasabahs as $nasabah) {
            $row = [
                'nasabah' => $nasabah,
                'minggu' => [],
                'total_kg' => 0,
                'total_nilai' => 0,
            ];

            foreach ($weeks as $i => [$weekStart, $weekEnd]) {
                $mingguTransaksi = $nasabah->transaksiSelesai
                    ->filter(fn($t) => $t->tanggal->format('Y-m-d') >= $weekStart && $t->tanggal->format('Y-m-d') <= $weekEnd);

                $kg = $mingguTransaksi->sum('berat_kg');
                $nilai = $mingguTransaksi->sum('total');

                $row['minggu'][$i + 1] = ['kg' => $kg, 'nilai' => $nilai];
                $row['total_kg'] += $kg;
                $row['total_nilai'] += $nilai;
            }

            $results[] = $row;
        }

        return [
            'weeks' => $weeks,
            'data' => $results,
        ];
    }

    public function getLaporanBulananDetail(int $bulan, int $tahun): array
    {
        $perNasabah = $this->getLaporanPerNasabah($bulan, $tahun);
        $perJenis = $this->getLaporanPerJenis($bulan, $tahun);
        $ringkasan = $this->getLaporanBulanan($bulan, $tahun);

        return [
            'per_nasabah' => $perNasabah,
            'per_jenis' => $perJenis,
            'ringkasan' => $ringkasan,
        ];
    }

    public function getLaporanKiloan(string $dari, string $sampai): array
    {
        return Transaksi::selesai()
            ->whereBetween('tanggal', [$dari, $sampai])
            ->join('jenis_sampah', 'transaksi.jenis_id', '=', 'jenis_sampah.id')
            ->selectRaw('tanggal, jenis_sampah.kode, jenis_sampah.nama,
                transaksi.harga_warga_kg as harga_kg,
                transaksi.berat_kg,
                transaksi.total as jumlah_harga,
                transaksi.harga_asli_kg')
            ->orderBy('tanggal')
            ->orderBy('jenis_sampah.kode')
            ->get()
            ->toArray();
    }

    protected function getWeeksInMonth(string $startOfMonth, string $endOfMonth): array
    {
        $weeks = [];
        $current = $startOfMonth;

        while ($current <= $endOfMonth) {
            $weekStart = $current;
            $weekEnd = min(date('Y-m-d', strtotime($current . ' +6 days')), $endOfMonth);
            $weeks[] = [$weekStart, $weekEnd];
            $current = date('Y-m-d', strtotime($weekEnd . ' +1 day'));
        }

        return $weeks;
    }
}