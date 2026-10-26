<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Penarikan;
use App\Models\JenisSampah;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getDashboardStats();
        $grafikBulanan = $this->getGrafikBulanan();
        $komposisiJenis = $this->getKomposisiJenis();

        return view('dashboard', compact(
            'stats',
            'grafikBulanan',
            'komposisiJenis'
        ));
    }

    protected function getDashboardStats(): array
    {
        $totalNasabahAktif = Nasabah::aktif()->count();
        $totalNasabah = Nasabah::count();
        $totalSampahSemua = Transaksi::selesai()->sum('berat_kg');
        $totalSampahBulanIni = Transaksi::selesai()->bulanIni()->sum('berat_kg');
        $totalSampahMingguIni = Transaksi::selesai()->mingguIni()->sum('berat_kg');
        $transaksiMingguIni = Transaksi::selesai()->mingguIni()->count();
        $totalNilaiBulanIni = Transaksi::selesai()->bulanIni()->sum('total');
        $totalSaldo = Nasabah::sum('saldo');
        $nasabahBaruBulanIni = Nasabah::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
        $nasabahTidakAktif = Nasabah::tidakAktif()->count();

        return [
            'total_nasabah' => $totalNasabah,
            'total_nasabah_aktif' => $totalNasabahAktif,
            'total_sampah_semua' => $totalSampahSemua,
            'total_sampah_bulan_ini' => $totalSampahBulanIni,
            'total_sampah_minggu_ini' => $totalSampahMingguIni,
            'transaksi_minggu_ini' => $transaksiMingguIni,
            'total_nilai_bulan_ini' => $totalNilaiBulanIni,
            'total_saldo' => $totalSaldo,
            'nasabah_baru_bulan_ini' => $nasabahBaruBulanIni,
            'nasabah_tidak_aktif' => $nasabahTidakAktif,
        ];
    }

    protected function getGrafikBulanan(): array
    {
        $data = Transaksi::selesai()
            ->whereYear('tanggal', now()->year)
            ->selectRaw('MONTH(tanggal) as bulan, COALESCE(SUM(berat_kg), 0) as kg')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $result[] = [
                'bulan' => str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'kg' => $data[$i]->kg ?? 0,
            ];
        }
        return $result;
    }

    protected function getKomposisiJenis(): array
    {
        return Transaksi::selesai()
            ->join('jenis_sampah', 'transaksi.jenis_id', '=', 'jenis_sampah.id')
            ->selectRaw('jenis_sampah.nama, jenis_sampah.ikon, jenis_sampah.kategori, COALESCE(SUM(transaksi.berat_kg), 0) as kg, COALESCE(SUM(transaksi.total), 0) as nilai')
            ->groupBy('jenis_sampah.id', 'jenis_sampah.nama', 'jenis_sampah.ikon', 'jenis_sampah.kategori')
            ->orderByDesc('kg')
            ->get()
            ->toArray();
    }

}