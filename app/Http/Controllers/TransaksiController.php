<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['nasabah', 'jenisSampah']);

        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhereHas('nasabah', fn($q) => $q->where('nama', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('jenis')) {
            $query->whereHas('jenisSampah', fn($q) => $q->where('nama', $request->jenis));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $transaksi = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(50)->withQueryString();

        $jenisSampah = JenisSampah::aktif()->orderBy('nama')->get();
        $stats = $this->getStatsHariIni();

        return view('transaksi.index', compact('transaksi', 'jenisSampah', 'stats'));
    }

    protected function getStatsHariIni(): array
    {
        $today = now()->format('Y-m-d');

        return [
            'setoran_hari_ini' => Transaksi::selesai()->whereDate('tanggal', $today)->count(),
            'berat_hari_ini' => Transaksi::selesai()->whereDate('tanggal', $today)->sum('berat_kg'),
            'nilai_hari_ini' => Transaksi::selesai()->whereDate('tanggal', $today)->sum('total'),
        ];
    }

    public function create()
    {
        Nasabah::perbaruiStatusJikaPerlu();
        $nasabah = Nasabah::orderBy('nama')->get();
        $jenisSampah = JenisSampah::aktif()->orderBy('kategori')->orderBy('nama')->get();

        return view('transaksi.create', compact('nasabah', 'jenisSampah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.jenis_id' => 'required|exists:jenis_sampah,id',
            'items.*.berat_kg' => 'nullable|numeric|min:0',

            'lainnya' => 'nullable|array',
            'lainnya.jenis' => 'nullable|string|max:100',
            'lainnya.berat_kg' => 'nullable|numeric|min:0',
            'lainnya.harga_kg' => 'nullable|numeric|min:0',
        ]);

        $validItems = collect($request->input('items', []))
            ->filter(function ($item) {
                return isset($item['berat_kg'])
                    && is_numeric($item['berat_kg'])
                    && (float) $item['berat_kg'] > 0;
            })
            ->map(function ($item) {
                return [
                    'jenis_id' => $item['jenis_id'],
                    'berat_kg' => (float) $item['berat_kg'],
                ];
            })
            ->values();

        $hasLainnya = $request->filled('lainnya.jenis')
            && $request->filled('lainnya.berat_kg')
            && $request->filled('lainnya.harga_kg')
            && (float) $request->input('lainnya.berat_kg') > 0;

        if ($validItems->isEmpty() && !$hasLainnya) {
            return back()
                ->withErrors([
                    'items' => 'Minimal satu berat harus lebih dari 0'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $validItems, $hasLainnya) {
            $nasabah = Nasabah::lockForUpdate()->findOrFail($request->nasabah_id);

            $totalSaldo = 0;
            $sequence = $this->lastSequence();
            $totalPoin = 0;

            foreach ($validItems as $item) {
                $jenis = JenisSampah::findOrFail($item['jenis_id']);

                $total = $item['berat_kg'] * $jenis->harga_warga_kg;
                $poinDapat = floor($jenis->poin_kg * $item['berat_kg']);

                $kode = 'TR' . str_pad(
                    (string) (++$sequence),
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                Transaksi::create([
                    'kode' => $kode,
                    'nasabah_id' => $nasabah->id,
                    'jenis_id' => $jenis->id,
                    'berat_kg' => $item['berat_kg'],
                    'harga_asli_kg' => $jenis->harga_asli_kg,
                    'harga_warga_kg' => $jenis->harga_warga_kg,
                    'total' => $total,
                    'poin_dapat' => $poinDapat,
                    'catatan' => $request->catatan,
                    'tanggal' => $request->tanggal,
                    'status' => 'Selesai',
                ]);

                $totalSaldo += $total;
                $totalPoin += $poinDapat;
            }

            if ($hasLainnya) {
                $lainnya = $request->lainnya;

                $hargaWarga = (float) $lainnya['harga_kg'];
                $beratLainnya = (float) $lainnya['berat_kg'];

                $total = $beratLainnya * $hargaWarga;
                $poinDapat = floor($beratLainnya);

                $kode = 'TR' . str_pad(
                    (string) (++$sequence),
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                $jenisLainnya = JenisSampah::firstOrCreate(
                    ['nama' => $lainnya['jenis']],
                    [
                        'kode' => Str::upper(Str::slug($lainnya['jenis'])),
                        'ikon' => '♻️',
                        'kategori' => 'Lainnya',
                        'harga_asli_kg' => $hargaWarga,
                        'harga_warga_kg' => $hargaWarga,
                        'poin_kg' => 1,
                        'aktif' => true,
                    ]
                );

                if (!$jenisLainnya->wasRecentlyCreated) {
                    $jenisLainnya->update([
                        'harga_asli_kg' => $hargaWarga,
                        'harga_warga_kg' => $hargaWarga,
                        'aktif' => true,
                    ]);
                }

                Transaksi::create([
                    'kode' => $kode,
                    'nasabah_id' => $nasabah->id,
                    'jenis_id' => $jenisLainnya->id,
                    'berat_kg' => $beratLainnya,
                    'harga_asli_kg' => $hargaWarga,
                    'harga_warga_kg' => $hargaWarga,
                    'total' => $total,
                    'poin_dapat' => $poinDapat,
                    'catatan' => $request->catatan,
                    'tanggal' => $request->tanggal,
                    'status' => 'Selesai',
                ]);

                $totalSaldo += $total;
                $totalPoin += $poinDapat;
            }

            $nasabah->increment('saldo', $totalSaldo);
            $nasabah->increment('poin', (int) $totalPoin);
            $nasabah->updateStatusOtomatis();
        });

        return redirect()
            ->route('riwayat.index')
            ->with('success', 'Setoran berhasil disimpan');
    }

    /**
     * Nomor urut terakhir dari kode TRxxx.
     */
    protected function lastSequence(): int
    {
        return (int) (
            Transaksi::where('kode', 'like', 'TR%')
                ->selectRaw('MAX(CAST(SUBSTRING(kode, 3) AS UNSIGNED)) as maks')
                ->value('maks') ?? 0
        );
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['nasabah', 'jenisSampah']);

        return view('transaksi.show', compact('transaksi'));
    }

    public function updateStatus(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'status' => 'required|in:Pending,Selesai,Dibatalkan',
        ]);

        DB::transaction(function () use ($transaksi, $request) {
            $oldStatus = $transaksi->status;
            $newStatus = $request->status;

            $transaksi->update(['status' => $newStatus]);

            if ($oldStatus === 'Selesai' && $newStatus !== 'Selesai') {
                $transaksi->nasabah->decrement('saldo', $transaksi->total);
                $transaksi->nasabah->decrement('poin', $transaksi->poin_dapat);
                $transaksi->nasabah->updateStatusOtomatis();
            } elseif ($oldStatus !== 'Selesai' && $newStatus === 'Selesai') {
                $transaksi->nasabah->increment('saldo', $transaksi->total);
                $transaksi->nasabah->increment('poin', $transaksi->poin_dapat);
                $transaksi->nasabah->updateStatusOtomatis();
            }
        });

        return back()->with('success', 'Status transaksi diperbarui');
    }

    public function destroy(Transaksi $transaksi)
    {
        DB::transaction(function () use ($transaksi) {
            if ($transaksi->status === 'Selesai') {
                $transaksi->nasabah->decrement('saldo', $transaksi->total);
                $transaksi->nasabah->decrement('poin', $transaksi->poin_dapat);
                $transaksi->nasabah->updateStatusOtomatis();
            }

            $transaksi->delete();
        });

        return back()->with('success', 'Transaksi dihapus');
    }

    public function destroyBatch(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:transaksi,id',
        ]);

        DB::transaction(function () use ($request) {
            $transaksis = Transaksi::whereIn('id', $request->ids)->get();

            foreach ($transaksis as $tr) {
                if ($tr->status === 'Selesai') {
                    $tr->nasabah->decrement('saldo', $tr->total);
                    $tr->nasabah->decrement('poin', $tr->poin_dapat);
                }
            }

            Transaksi::whereIn('id', $request->ids)->delete();

            foreach ($transaksis as $tr) {
                $tr->nasabah->updateStatusOtomatis();
            }
        });

        return back()->with(
            'success',
            count($request->ids) . ' transaksi dihapus'
        );
    }
}
