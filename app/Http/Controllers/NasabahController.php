<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NasabahExport;

class NasabahController extends Controller
{
    public function index(Request $request)
    {
        Nasabah::perbaruiStatusJikaPerlu();
        $query = Nasabah::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $nasabah = $query->orderBy('kode')->paginate(20)->withQueryString();

        $stats = [
            'total' => Nasabah::count(),
            'aktif' => Nasabah::aktif()->count(),
            'baru_bulan_ini' => Nasabah::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'tidak_aktif' => Nasabah::tidakAktif()->count(),
        ];

        $setoranMingguIni = Transaksi::selesai()
            ->mingguIni()
            ->selectRaw('nasabah_id, COUNT(*) as total')
            ->groupBy('nasabah_id')
            ->pluck('total', 'nasabah_id')
            ->toArray();

        return view('nasabah.index', compact('nasabah', 'stats', 'setoranMingguIni'));
    }

    public function create()
    {
        $nextKode = $this->generateNextKode();
        return view('nasabah.create', compact('nextKode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'saldo' => 'nullable|numeric|min:0',
            'poin' => 'nullable|integer|min:0',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        $kode = $this->generateNextKode();

        DB::transaction(function () use ($request, $kode) {
            Nasabah::create([
                'kode' => $kode,
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'saldo' => $request->saldo ?? 0,
                'poin' => $request->poin ?? 0,
                'status' => $request->status,
            ]);
        });

        return redirect()->route('nasabah.index')->with('success', 'Nasabah berhasil ditambahkan');
    }

    public function show(Nasabah $nasabah)
    {
        $transaksiCount = $nasabah->transaksiSelesai()->count();
        $transaksiTotal = $nasabah->transaksiSelesai()->sum('total');

        return view('nasabah.show', compact('nasabah', 'transaksiCount', 'transaksiTotal'));
    }

    public function edit(Nasabah $nasabah)
    {
        return view('nasabah.edit', compact('nasabah'));
    }

    public function update(Request $request, Nasabah $nasabah)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'saldo' => 'nullable|numeric|min:0',
            'status' => 'required|in:Aktif,Tidak Aktif',
        ]);

        $nasabah->update([
            'nama' => $request->nama,
            'telepon' => $request->telepon,
            'alamat' => $request->alamat,
            'saldo' => $request->saldo,
            'status' => $request->status,
        ]);

        return redirect()->route('nasabah.index')->with('success', 'Data nasabah berhasil diperbarui');
    }

    public function destroy(Nasabah $nasabah)
    {
        if ($nasabah->transaksi()->exists() || $nasabah->penarikan()->exists()) {
            return back()->with('error', 'Nasabah memiliki histori transaksi/penarikan. Tidak dapat dihapus. Gunakan nonaktifkan saja.');
        }

        $nasabah->delete();
        return redirect()->route('nasabah.index')->with('success', 'Nasabah berhasil dihapus');
    }

    public function export(Request $request)
    {
        return Excel::download(new NasabahExport($request->all()), 'nasabah-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportCsv(Request $request)
    {
        $filename = 'nasabah-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Kode', 'Nama', 'Telepon', 'Alamat', 'Saldo', 'Poin', 'Status', 'Setoran Terakhir']);

            $query = Nasabah::query();
            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%");
                });
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->orderBy('kode')->chunk(200, function ($nasabahs) use ($file) {
                foreach ($nasabahs as $n) {
                    fputcsv($file, [
                        $n->kode,
                        $n->nama,
                        $n->telepon,
                        $n->alamat,
                        $n->saldo,
                        $n->poin,
                        $n->status,
                        $n->last_deposit_at?->format('Y-m-d') ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function generateNextKode(): string
    {
        $last = Nasabah::where('kode', 'like', 'NK.%')
            ->orderByRaw('CAST(REPLACE(kode, "NK.", "") AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $last ? (int)str_replace('NK.', '', $last->kode) + 1 : 1;
        return 'NK.' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }
}