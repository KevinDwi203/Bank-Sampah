@extends('layouts.app')

@section('title', 'Pengaturan')
@section('pageTitle', 'Pengaturan')

@section('content')
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>🏢</span> Identitas Bank Sampah</div>
        </div>

        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nama Utama</label>
                    <input type="text" class="form-control" name="nama_utama" value="{{ $settings['nama_utama'] ?? 'Bank Sampah' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Subjudul</label>
                    <input type="text" class="form-control" name="subjudul" value="{{ $settings['subjudul'] ?? 'Hijau Lestari' }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Title Kecil</label>
                <input type="text" class="form-control" name="title_kecil" value="{{ $settings['title_kecil'] ?? '' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="2">{{ $settings['alamat'] ?? '' }}</textarea>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" class="form-control" name="telepon" value="{{ $settings['telepon'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="{{ $settings['email'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Rekening</label>
                    <input type="text" class="form-control" name="nomor_rekening" value="{{ $settings['nomor_rekening'] ?? '' }}">
                </div>
            </div>

            <div class="modal-footer" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">💾 Simpan Identitas</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>🖼️</span> Logo Bank Sampah</div>
        </div>

        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Upload Logo (PNG, JPG, WebP - Max 2MB)</label>
                <input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/webp">
            </div>

            @if (!empty($settings['logo_path']))
                <div style="margin-bottom: 16px;">
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:8px;">Logo Saat Ini:</p>
                    <img src="{{ asset('storage/' . ($settings['logo_path'] ?? '')) }}" alt="Logo" style="max-height: 100px; border: 1px solid var(--border); border-radius: 8px; padding: 8px; background: white;">
                </div>
            @endif

            <div class="modal-footer" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">🖼️ Upload Logo</button>
            </div>
        </form>

        @if (!empty($settings['logo_path']))
            <form action="{{ route('setting.logo.destroy') }}" method="POST" style="margin-top: 12px;"
                  onsubmit="return confirm('Hapus logo? Sidebar akan kembali memakai ikon bawaan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline" style="color:#c62828; border-color:#c62828;">🗑️ Hapus Logo</button>
            </form>
        @endif
    </div>
@endsection