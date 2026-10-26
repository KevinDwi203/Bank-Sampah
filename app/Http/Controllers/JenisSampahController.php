<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use Illuminate\Http\Request;

class JenisSampahController extends Controller
{
    public function index(Request $request)
    {
        $jenisSampah = JenisSampah::query()
            ->where('aktif', true)
            ->when($request->filled('q'), fn($q) => $q->where('nama', 'like', '%' . $request->q . '%'))
            ->orderBy('kategori')->orderBy('nama')
            ->paginate(20)->withQueryString();
        return view('jenis-sampah.index', compact('jenisSampah'));
    }

    public function create()
    {
        return view('jenis-sampah.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:jenis_sampah,nama',
            'kode' => 'nullable|string|max:20|unique:jenis_sampah,kode',
            'ikon' => 'nullable|string|max:10',
            'kategori' => 'required|string|max:50',
            'harga_asli_kg' => 'required|numeric|min:0',
            'harga_warga_kg' => 'required|numeric|min:0',
            'poin_kg' => 'required|integer|min:1',
            'aktif' => 'boolean',
        ]);

        JenisSampah::create($request->except('aktif') + ['aktif' => $request->boolean('aktif')]);

        return redirect()->route('jenis-sampah.index')->with('success', 'Jenis sampah berhasil ditambahkan');
    }

    public function edit(JenisSampah $jenisSampah)
    {
        return view('jenis-sampah.edit', compact('jenisSampah'));
    }

    public function update(Request $request, JenisSampah $jenisSampah)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:jenis_sampah,nama,' . $jenisSampah->id,
            'kode' => 'nullable|string|max:20|unique:jenis_sampah,kode,' . $jenisSampah->id,
            'ikon' => 'nullable|string|max:10',
            'kategori' => 'required|string|max:50',
            'harga_asli_kg' => 'required|numeric|min:0',
            'harga_warga_kg' => 'required|numeric|min:0',
            'poin_kg' => 'required|integer|min:1',
            'aktif' => 'boolean',
        ]);

        $jenisSampah->update($request->except('aktif') + ['aktif' => $request->boolean('aktif')]);

        return redirect()->route('jenis-sampah.index')->with('success', 'Jenis sampah berhasil diperbarui');
    }

    public function destroy(JenisSampah $jenisSampah)
    {
        if ($jenisSampah->transaksi()->exists()) {
            $jenisSampah->update(['aktif' => false]);
            return back()->with('warning', 'Jenis sampah memiliki transaksi. Status diubah menjadi tidak aktif.');
        }

        $jenisSampah->delete();
        return redirect()->route('jenis-sampah.index')->with('success', 'Jenis sampah berhasil dihapus');
    }
}