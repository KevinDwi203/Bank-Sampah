<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected array $fields = ['nama_utama', 'subjudul', 'title_kecil', 'alamat', 'telepon', 'email', 'nomor_rekening'];

    public function index()
    {
        $settings = Setting::whereIn('group', ['identitas', 'logo'])->pluck('value', 'key')->toArray();
        return view('setting.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_utama' => 'nullable|string|max:100',
            'subjudul' => 'nullable|string|max:100',
            'title_kecil' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'nomor_rekening' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        // Hanya field yang dikenal (sebelumnya _token & _method ikut tersimpan ke tabel settings)
        foreach ($this->fields as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key) ?? '', 'string', 'identitas');
            }
        }

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo_path');
            $path = $request->file('logo')->store('logos', 'public');

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            Setting::set('logo_path', $path, 'string', 'logo');
            Setting::set('logo_url', Storage::url($path), 'string', 'logo');
        }

        return back()->with('success', 'Pengaturan berhasil disimpan');
    }

    public function destroyLogo()
    {
        $logoPath = Setting::get('logo_path');

        // Hapus file logo dari penyimpanan (jika masih ada)
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            Storage::disk('public')->delete($logoPath);
        }

        // Kosongkan pengaturan logo; sidebar otomatis kembali memakai ikon bawaan
        Setting::set('logo_path', '', 'string', 'logo');
        Setting::set('logo_url', '', 'string', 'logo');

        return back()->with('success', 'Logo berhasil dihapus');
    }
}