<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\LaporanItem;
use App\Models\JenisSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        // Ambil data laporan manual untuk bulan & tahun yang dipilih
        $laporans = Laporan::with(['items.jenisSampah', 'user'])
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Hitung ringkasan statistik dari laporan manual pada periode ini
        $periodQuery = Laporan::whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        $totalKg = (float) $periodQuery->sum('total_berat_kg');
        $totalNilai = (float) $periodQuery->sum('total_nilai');
        $totalLaporan = $periodQuery->count();

        // Hitung rekapitulasi per jenis sampah dari seluruh item laporan manual pada periode ini
        $items = LaporanItem::whereHas('laporan', function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        })->get();

        $rekapPerJenis = $items->groupBy('nama_jenis')->map(function ($group, $nama) {
            $kg = (float) $group->sum('berat_kg');
            $nilai = (float) $group->sum('subtotal');
            $avgHarga = $kg > 0 ? $nilai / $kg : 0;
            return [
                'nama' => $nama,
                'total_kg' => $kg,
                'avg_harga' => $avgHarga,
                'total_nilai' => $nilai,
            ];
        })->values();

        return view('laporan.index', compact(
            'laporans',
            'totalKg',
            'totalNilai',
            'totalLaporan',
            'rekapPerJenis',
            'bulan',
            'tahun'
        ));
    }

    public function create()
    {
        $jenisSampah = JenisSampah::aktif()->orderBy('kategori')->orderBy('nama')->get();
        $nextKode = $this->generateNextKode();

        return view('laporan.create', compact('jenisSampah', 'nextKode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'items' => 'nullable|array',
            'lainnya' => 'nullable|array',
        ]);

        $itemsToSave = [];
        $totalBerat = 0;
        $totalNilai = 0;

        // Proses jenis sampah dari grid/katalog
        if ($request->filled('items') && is_array($request->items)) {
            foreach ($request->items as $jenisId => $itemData) {
                $berat = (float) ($itemData['berat_kg'] ?? 0);
                if ($berat > 0) {
                    $jenis = JenisSampah::find($jenisId);
                    $harga = isset($itemData['harga_kg']) && is_numeric($itemData['harga_kg']) && (float)$itemData['harga_kg'] > 0
                        ? (float) $itemData['harga_kg']
                        : ($jenis?->harga_asli_kg ?? 0);
                    $subtotal = $berat * $harga;

                    $itemsToSave[] = [
                        'jenis_id' => $jenisId,
                        'nama_jenis' => $jenis ? $jenis->nama : 'Sampah #' . $jenisId,
                        'berat_kg' => $berat,
                        'harga_kg' => $harga,
                        'subtotal' => $subtotal,
                    ];

                    $totalBerat += $berat;
                    $totalNilai += $subtotal;
                }
            }
        }

        // Proses jenis sampah tambahan (Lainnya)
        if ($request->filled('lainnya.jenis') && (float) ($request->input('lainnya.berat_kg') ?? 0) > 0) {
            $namaLainnya = trim($request->input('lainnya.jenis'));
            $beratLainnya = (float) $request->input('lainnya.berat_kg');
            $hargaLainnya = (float) ($request->input('lainnya.harga_kg') ?? 0);
            $subtotalLainnya = $beratLainnya * $hargaLainnya;

            $itemsToSave[] = [
                'jenis_id' => null,
                'nama_jenis' => $namaLainnya,
                'berat_kg' => $beratLainnya,
                'harga_kg' => $hargaLainnya,
                'subtotal' => $subtotalLainnya,
            ];

            $totalBerat += $beratLainnya;
            $totalNilai += $subtotalLainnya;
        }

        if (empty($itemsToSave)) {
            return back()->withInput()->with('error', 'Silakan masukkan minimal 1 jenis sampah dengan berat lebih dari 0 kg.');
        }

        $kode = $this->generateNextKode();
        $isDummy = auth()->check() ? (bool) auth()->user()->is_dummy : false;

        DB::transaction(function () use ($request, $kode, $totalBerat, $totalNilai, $itemsToSave, $isDummy) {
            $laporan = Laporan::create([
                'kode' => $kode,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'total_berat_kg' => $totalBerat,
                'total_nilai' => $totalNilai,
                'catatan' => $request->catatan,
                'user_id' => auth()->id(),
                'is_dummy' => $isDummy,
            ]);

            foreach ($itemsToSave as $it) {
                $laporan->items()->create($it);
            }
        });

        return redirect()->route('laporan.index', [
            'bulan' => date('n', strtotime($request->tanggal)),
            'tahun' => date('Y', strtotime($request->tanggal)),
        ])->with('success', "Laporan {$kode} berhasil disimpan! (Total: " . number_format($totalBerat, 2, ',', '.') . " kg | Rp " . number_format($totalNilai, 0, ',', '.') . ")");
    }

    public function show(Laporan $laporan)
    {
        $laporan->load(['items.jenisSampah', 'user']);
        return view('laporan.show', compact('laporan'));
    }

    public function edit(Laporan $laporan)
    {
        $laporan->load('items');
        $jenisSampah = JenisSampah::aktif()->orderBy('kategori')->orderBy('nama')->get();
        $existingItems = $laporan->items->keyBy('jenis_id');
        $lainnyaItem = $laporan->items->whereNull('jenis_id')->first();

        return view('laporan.edit', compact('laporan', 'jenisSampah', 'existingItems', 'lainnyaItem'));
    }

    public function update(Request $request, Laporan $laporan)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'catatan' => 'nullable|string',
            'items' => 'nullable|array',
            'lainnya' => 'nullable|array',
        ]);

        $itemsToSave = [];
        $totalBerat = 0;
        $totalNilai = 0;

        if ($request->filled('items') && is_array($request->items)) {
            foreach ($request->items as $jenisId => $itemData) {
                $berat = (float) ($itemData['berat_kg'] ?? 0);
                if ($berat > 0) {
                    $jenis = JenisSampah::find($jenisId);
                    $harga = isset($itemData['harga_kg']) && is_numeric($itemData['harga_kg']) && (float)$itemData['harga_kg'] > 0
                        ? (float) $itemData['harga_kg']
                        : ($jenis?->harga_asli_kg ?? 0);
                    $subtotal = $berat * $harga;

                    $itemsToSave[] = [
                        'jenis_id' => $jenisId,
                        'nama_jenis' => $jenis ? $jenis->nama : 'Sampah #' . $jenisId,
                        'berat_kg' => $berat,
                        'harga_kg' => $harga,
                        'subtotal' => $subtotal,
                    ];

                    $totalBerat += $berat;
                    $totalNilai += $subtotal;
                }
            }
        }

        if ($request->filled('lainnya.jenis') && (float) ($request->input('lainnya.berat_kg') ?? 0) > 0) {
            $namaLainnya = trim($request->input('lainnya.jenis'));
            $beratLainnya = (float) $request->input('lainnya.berat_kg');
            $hargaLainnya = (float) ($request->input('lainnya.harga_kg') ?? 0);
            $subtotalLainnya = $beratLainnya * $hargaLainnya;

            $itemsToSave[] = [
                'jenis_id' => null,
                'nama_jenis' => $namaLainnya,
                'berat_kg' => $beratLainnya,
                'harga_kg' => $hargaLainnya,
                'subtotal' => $subtotalLainnya,
            ];

            $totalBerat += $beratLainnya;
            $totalNilai += $subtotalLainnya;
        }

        if (empty($itemsToSave)) {
            return back()->withInput()->with('error', 'Silakan masukkan minimal 1 jenis sampah dengan berat lebih dari 0 kg.');
        }

        DB::transaction(function () use ($request, $laporan, $totalBerat, $totalNilai, $itemsToSave) {
            $laporan->update([
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'total_berat_kg' => $totalBerat,
                'total_nilai' => $totalNilai,
                'catatan' => $request->catatan,
            ]);

            $laporan->items()->delete();

            foreach ($itemsToSave as $it) {
                $laporan->items()->create($it);
            }
        });

        return redirect()->route('laporan.index', [
            'bulan' => date('n', strtotime($request->tanggal)),
            'tahun' => date('Y', strtotime($request->tanggal)),
        ])->with('success', "Laporan {$laporan->kode} berhasil diperbarui!");
    }

    public function destroy(Laporan $laporan)
    {
        $kode = $laporan->kode;
        $laporan->delete();

        return redirect()->route('laporan.index')->with('success', "Laporan {$kode} berhasil dihapus.");
    }

    public function pdfRekap(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $laporans = Laporan::with('items')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $items = LaporanItem::whereHas('laporan', function ($q) use ($bulan, $tahun) {
            $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        })->get();

        $rekapItems = $items->groupBy('nama_jenis')->map(function ($group, $nama) {
            $kg = (float) $group->sum('berat_kg');
            $nilai = (float) $group->sum('subtotal');
            $avgHarga = $kg > 0 ? $nilai / $kg : 0;
            return [
                'nama' => $nama,
                'total_kg' => $kg,
                'harga_asli_kg' => $avgHarga,
                'total_nilai' => $nilai,
            ];
        })->values()->toArray();

        $totalKg = array_sum(array_column($rekapItems, 'total_kg'));
        $totalNilai = array_sum(array_column($rekapItems, 'total_nilai'));

        $rekap = [
            'items' => $rekapItems,
            'total_kg' => $totalKg,
            'total_nilai' => $totalNilai,
        ];

        $dari = sprintf('%04d-%02d-01', $tahun, $bulan);
        $sampai = date('Y-m-t', strtotime($dari));

        $pdf = Pdf::loadView('laporan.pdf.rekap', compact('rekap', 'laporans', 'dari', 'sampai', 'bulan', 'tahun'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("rekap-laporan-sampah-{$tahun}-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . ".pdf");
    }

    protected function generateNextKode(): string
    {
        $prefix = 'LAP.' . now()->format('Ym') . '.';
        $last = Laporan::withoutGlobalScopes()
            ->where('kode', 'like', $prefix . '%')
            ->orderByRaw('CAST(REPLACE(kode, "' . $prefix . '", "") AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $last ? (int)str_replace($prefix, '', $last->kode) + 1 : 1;
        return $prefix . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
