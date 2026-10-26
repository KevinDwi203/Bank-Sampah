<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\User;
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

        $nasabah = $query->with('user')->orderBy('kode')->paginate(20)->withQueryString();

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
        $isDummy = auth()->check() ? (bool) auth()->user()->is_dummy : false;
        $nomorKartu = $this->generateNomorKartu($isDummy);
        $defaultUsername = $this->generateNasabahUsername($isDummy, $nomorKartu);

        return view('nasabah.create', compact('nextKode', 'defaultUsername'));
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
            'username' => 'nullable|string|max:100|unique:users,username',
            'password' => 'nullable|string|min:4',
        ]);

        $isDummy = auth()->check() ? (bool) auth()->user()->is_dummy : false;
        $kode = $this->generateNextKode();
        $nomorKartu = $this->generateNomorKartu($isDummy);

        $username = $request->filled('username')
            ? trim($request->username)
            : $this->generateNasabahUsername($isDummy, $nomorKartu);

        $password = $request->filled('password') ? $request->password : 'nasabah123';

        DB::transaction(function () use ($request, $kode, $nomorKartu, $isDummy, $username, $password) {
            $nasabah = Nasabah::create([
                'kode' => $kode,
                'nomor_kartu' => $nomorKartu,
                'nama' => $request->nama,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'saldo' => $request->saldo ?? 0,
                'poin' => $request->poin ?? 0,
                'status' => $request->status,
                'is_dummy' => $isDummy,
            ]);

            User::create([
                'username' => $username,
                'password' => $password,
                'nama' => $nasabah->nama,
                'telepon' => $nasabah->telepon,
                'role' => 'Nasabah',
                'aktif' => true,
                'is_dummy' => $isDummy,
                'nasabah_id' => $nasabah->id,
            ]);
        });

        $successMsg = "Nasabah '{$request->nama}' berhasil ditambahkan! Akun Login: Username: '{$username}' | Password: '{$password}'";

        return redirect()->route('nasabah.index')->with('success', $successMsg);
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

        // Selaraskan ke user account nasabah
        User::withoutGlobalScopes()
            ->where('nasabah_id', $nasabah->id)
            ->update([
                'nama' => $request->nama,
                'telepon' => $request->telepon,
            ]);

        return redirect()->route('nasabah.index')->with('success', 'Data nasabah berhasil diperbarui');
    }

    public function destroy(Nasabah $nasabah)
    {
        if ($nasabah->transaksi()->exists() || $nasabah->penarikan()->exists()) {
            return back()->with('error', 'Nasabah memiliki histori transaksi/penarikan. Tidak dapat dihapus. Gunakan nonaktifkan saja.');
        }

        // Hapus akun user yang terhubung
        User::withoutGlobalScopes()->where('nasabah_id', $nasabah->id)->delete();

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
        $isDummy = auth()->check() ? (bool) auth()->user()->is_dummy : false;
        $prefix = $isDummy ? 'NK.TEST-' : 'NK.';

        $last = Nasabah::withoutGlobalScopes()
            ->where('kode', 'like', $prefix . '%')
            ->orderByRaw('CAST(REPLACE(kode, "' . $prefix . '", "") AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $last ? (int)str_replace($prefix, '', $last->kode) + 1 : 1;
        return $prefix . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected function generateNomorKartu(bool $isDummy): string
    {
        if ($isDummy) {
            $last = Nasabah::withoutGlobalScopes()
                ->where('nomor_kartu', 'like', 'TEST%')
                ->orderByRaw('CAST(REPLACE(nomor_kartu, "TEST", "") AS UNSIGNED) DESC')
                ->first();
            $next = $last ? (int)str_replace('TEST', '', $last->nomor_kartu) + 1 : 1;
            return 'TEST' . str_pad((string)$next, 2, '0', STR_PAD_LEFT);
        }

        $last = Nasabah::withoutGlobalScopes()
            ->whereRaw('nomor_kartu REGEXP "^[0-9]+$"')
            ->orderByRaw('CAST(nomor_kartu AS UNSIGNED) DESC')
            ->first();

        $next = $last ? (int)$last->nomor_kartu + 1 : 1;
        return str_pad((string)$next, 2, '0', STR_PAD_LEFT);
    }

    protected function generateNasabahUsername(bool $isDummy, string $nomorKartu): string
    {
        if ($isDummy) {
            $base = 'testnasabah';
            if (!User::where('username', $base)->exists()) {
                return $base;
            }
            $i = 2;
            while (User::where('username', $base . $i)->exists()) {
                $i++;
            }
            return $base . $i;
        }

        $cleanCard = strtolower(preg_replace('/[^0-9]/', '', $nomorKartu));
        $candidate = 'nk' . ($cleanCard !== '' ? $cleanCard : 'new');
        if (!User::where('username', $candidate)->exists()) {
            return $candidate;
        }

        $i = 1;
        while (User::where('username', $candidate . '_' . $i)->exists()) {
            $i++;
        }
        return $candidate . '_' . $i;
    }
}