<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenarikanController extends Controller
{
    public function index(Request $request)
    {
        $query = Penarikan::with('nasabah');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $penarikan = $query->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'bulan_ini' => Penarikan::selesai()->bulanIni()->count(),
            'total_nominal' => Penarikan::selesai()->bulanIni()->sum('jumlah'),
            'pending' => Penarikan::pending()->count(),
        ];

        return view('penarikan.index', compact('penarikan', 'stats'));
    }

    public function create()
    {
        $nasabah = Nasabah::aktif()->where('saldo', '>', 0)->orderBy('nama')->get();
        return view('penarikan.create', compact('nasabah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'jumlah' => 'required|numeric|min:1000',
            'metode' => 'required|in:Tunai,Transfer Bank',
            'catatan' => 'nullable|string',
            'tanggal' => 'required|date',
        ]);

        $error = null;

        DB::transaction(function () use ($request, &$error) {
            $nasabah = Nasabah::lockForUpdate()->findOrFail($request->nasabah_id);

            if ($nasabah->saldo < $request->jumlah) {
                $error = 'Saldo tidak mencukupi. Saldo saat ini: Rp ' . number_format($nasabah->saldo, 0, ',', '.');
                return;
            }

            $lastNo = (int) (Penarikan::selectRaw('MAX(CAST(SUBSTRING(kode, 3) AS UNSIGNED)) as maks')->value('maks') ?? 0);
            $kode = 'WD' . str_pad((string)($lastNo + 1), 3, '0', STR_PAD_LEFT);

            Penarikan::create([
                'kode' => $kode,
                'nasabah_id' => $nasabah->id,
                'jumlah' => $request->jumlah,
                'metode' => $request->metode,
                'catatan' => $request->catatan,
                'tanggal' => $request->tanggal,
                'status' => 'Selesai',
            ]);

            $nasabah->decrement('saldo', $request->jumlah);
        });

        if ($error) {
            return back()->withErrors(['jumlah' => $error])->withInput();
        }

        return redirect()->route('penarikan.index')->with('success', 'Penarikan berhasil diproses');
    }

    public function show(Penarikan $penarikan)
    {
        $penarikan->load('nasabah');
        return view('penarikan.show', compact('penarikan'));
    }

    public function updateStatus(Request $request, Penarikan $penarikan)
    {
        $request->validate([
            'status' => 'required|in:Pending,Selesai,Dibatalkan',
        ]);

        $error = null;

        DB::transaction(function () use ($penarikan, $request, &$error) {
            $oldStatus = $penarikan->status;
            $newStatus = $request->status;

            if ($oldStatus === 'Selesai' && $newStatus !== 'Selesai') {
                $penarikan->nasabah->increment('saldo', $penarikan->jumlah);
            } elseif ($oldStatus !== 'Selesai' && $newStatus === 'Selesai') {
                if ($penarikan->nasabah->saldo < $penarikan->jumlah) {
                    $error = 'Saldo tidak mencukupi untuk menyelesaikan penarikan';
                    return;
                }
                $penarikan->nasabah->decrement('saldo', $penarikan->jumlah);
            }

            $penarikan->update(['status' => $newStatus]);
        });

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'Status penarikan diperbarui');
    }

    public function destroy(Penarikan $penarikan)
    {
        if ($penarikan->status === 'Selesai') {
            return back()->with('error', 'Penarikan yang sudah selesai tidak bisa dihapus. Batalkan terlebih dahulu.');
        }

        $penarikan->delete();
        return back()->with('success', 'Penarikan dihapus');
    }
}