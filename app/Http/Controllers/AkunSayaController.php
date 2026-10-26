<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\Transaksi;

class AkunSayaController extends Controller
{
    public function index()
    {
        // Semua data diambil dari akun yang sedang login, BUKAN dari parameter URL,
        // sehingga nasabah tidak mungkin melihat data nasabah lain.
        $nasabah = auth()->user()->nasabah;

        abort_if(!$nasabah, 403, 'Akun ini belum dihubungkan dengan data nasabah. Hubungi petugas.');

        // Setoran milik nasabah ini, digabung per jenis sampah (satu baris per jenis).
        $rows = Transaksi::selesai()
            ->where('transaksi.nasabah_id', $nasabah->id)
            ->join('jenis_sampah', 'transaksi.jenis_id', '=', 'jenis_sampah.id')
            ->selectRaw('jenis_sampah.nama, jenis_sampah.ikon,
                COALESCE(SUM(transaksi.berat_kg), 0) as total_kg,
                COALESCE(SUM(transaksi.total), 0) as total_nilai')
            ->groupBy('jenis_sampah.id', 'jenis_sampah.nama', 'jenis_sampah.ikon')
            ->orderBy('jenis_sampah.nama')
            ->get();

        $setoran = $rows->map(function ($r) {
            $kg = (float) $r->total_kg;
            $nilai = (float) $r->total_nilai;

            return [
                'nama' => $r->nama,
                'ikon' => $r->ikon,
                'total_kg' => $kg,
                // harga warga/kg yang dipakai saat transaksi (rata-rata tertimbang bila pernah berubah)
                'harga_kg' => $kg > 0 ? $nilai / $kg : null,
                'total_nilai' => $nilai,
            ];
        });

        $totalKg = $setoran->sum('total_kg');
        $totalNilai = $setoran->sum('total_nilai');

        $hargaWarga = JenisSampah::aktif()->orderBy('kategori')->orderBy('nama')->get();

        return view('akun-saya', compact('nasabah', 'setoran', 'totalKg', 'totalNilai', 'hargaWarga'));
    }
}
