<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use Illuminate\Http\Request;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $query = ArusKas::with('user');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $arusKas = $query->latest('tanggal')->latest('id')->paginate(20)->withQueryString();

        $saldoAwal = ArusKas::where('tanggal', '<', $request->get('dari', now()->startOfMonth()))
            ->sum('masuk') - ArusKas::where('tanggal', '<', $request->get('dari', now()->startOfMonth()))
            ->sum('keluar');

        $stats = [
            'total_masuk' => $query->clone()->sum('masuk'),
            'total_keluar' => $query->clone()->sum('keluar'),
            'saldo_akhir' => $saldoAwal + $query->clone()->sum('masuk') - $query->clone()->sum('keluar'),
        ];

        return view('arus-kas.index', compact('arusKas', 'stats'));
    }

    public function create()
    {
        return view('arus-kas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'kategori' => 'required|in:pengeluaran_operasional,pembayaran_pengurus,pembelian_peralatan,biaya_transport,biaya_konsumsi,lainnya',
            'masuk' => 'nullable|numeric|min:0',
            'keluar' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        if (!$request->filled('masuk') && !$request->filled('keluar')) {
            return back()->withErrors(['masuk' => 'Isi minimal masuk atau keluar'])->withInput();
        }

        $saldoSebelum = ArusKas::where('tanggal', '<=', $request->tanggal)
            ->where('id', '<', ArusKas::where('tanggal', '<=', $request->tanggal)->max('id') ?? 0)
            ->sum('masuk') - ArusKas::where('tanggal', '<=', $request->tanggal)
            ->where('id', '<', ArusKas::where('tanggal', '<=', $request->tanggal)->max('id') ?? 0)
            ->sum('keluar');

        $saldoBaru = $saldoSebelum + ($request->masuk ?? 0) - ($request->keluar ?? 0);

        ArusKas::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'kategori' => $request->kategori,
            'masuk' => $request->masuk ?? 0,
            'keluar' => $request->keluar ?? 0,
            'saldo' => $saldoBaru,
            'catatan' => $request->catatan,
            'user_id' => auth()->id(),
        ]);

        $this->updateSaldoBerikutnya($request->tanggal);

        return redirect()->route('arus-kas.index')->with('success', 'Arus kas berhasil ditambahkan');
    }

    public function edit(ArusKas $arusKas)
    {
        return view('arus-kas.edit', compact('arusKas'));
    }

    public function update(Request $request, ArusKas $arusKas)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'kategori' => 'required|in:pengeluaran_operasional,pembayaran_pengurus,pembelian_peralatan,biaya_transport,biaya_konsumsi,lainnya',
            'masuk' => 'nullable|numeric|min:0',
            'keluar' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $tanggalLama = $arusKas->tanggal;
        $arusKas->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'kategori' => $request->kategori,
            'masuk' => $request->masuk ?? 0,
            'keluar' => $request->keluar ?? 0,
            'catatan' => $request->catatan,
        ]);

        $this->updateSaldoBerikutnya($tanggalLama);
        if ($request->tanggal !== $tanggalLama) {
            $this->updateSaldoBerikutnya($request->tanggal);
        }

        return redirect()->route('arus-kas.index')->with('success', 'Arus kas berhasil diperbarui');
    }

    public function destroy(ArusKas $arusKas)
    {
        $tanggal = $arusKas->tanggal;
        $arusKas->delete();
        $this->updateSaldoBerikutnya($tanggal);

        return back()->with('success', 'Arus kas dihapus');
    }

    protected function updateSaldoBerikutnya($dariTanggal)
    {
        $records = ArusKas::where('tanggal', '>=', $dariTanggal)->orderBy('tanggal')->orderBy('id')->get();

        $saldo = ArusKas::where('tanggal', '<', $dariTanggal)
            ->sum('masuk') - ArusKas::where('tanggal', '<', $dariTanggal)->sum('keluar');

        foreach ($records as $record) {
            $saldo += $record->masuk - $record->keluar;
            $record->update(['saldo' => $saldo]);
        }
    }
}