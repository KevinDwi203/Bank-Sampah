<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\Penarikan;

class TabunganController extends Controller
{
    public function index()
    {
        $nasabah = Nasabah::withSum('transaksiSelesai as total_setoran', 'total')
            ->withSum('penarikanSelesai as total_penarikan', 'jumlah')
            ->orderBy('nama')
            ->paginate(20);

        $stats = [
            'total_saldo' => Nasabah::sum('saldo'),
            'uang_masuk_bulan_ini' => Transaksi::selesai()->bulanIni()->sum('total'),
            'uang_ditarik_bulan_ini' => Penarikan::selesai()->bulanIni()->sum('jumlah'),
            'nasabah_aktif' => Nasabah::aktif()->count(),
            'nasabah_tidak_aktif' => Nasabah::tidakAktif()->count(),
        ];

        return view('tabungan.index', compact('nasabah', 'stats'));
    }

    public function show(Nasabah $nasabah)
    {
        $transaksi = $nasabah->transaksiSelesai()->latest('tanggal')->paginate(15);
        $penarikan = $nasabah->penarikanSelesai()->latest('tanggal')->paginate(15);

        return view('tabungan.show', compact('nasabah', 'transaksi', 'penarikan'));
    }
}