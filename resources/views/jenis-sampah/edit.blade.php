@extends('layouts.app')

@section('title', 'Edit Jenis Sampah')
@section('pageTitle', 'Edit Jenis Sampah')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>✏️</span> Edit Jenis Sampah</div>
        </div>

        <form action="{{ route('jenis-sampah.update', $jenisSampah) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nama Jenis <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="nama" required placeholder="Contoh: Gelas Plastik" value="{{ old('nama', $jenisSampah->nama) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kode</label>
                    <input type="text" class="form-control" name="kode" placeholder="Contoh: GP" value="{{ old('kode', $jenisSampah->kode) }}">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Ikon</label>
                    <input type="text" class="form-control" name="ikon" placeholder="Contoh: 🥤" value="{{ old('ikon', $jenisSampah->ikon) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategori <span style="color:red">*</span></label>
                    <select class="form-control" name="kategori" required>
                        <option value="Plastik" {{ old('kategori', $jenisSampah->kategori) === 'Plastik' ? 'selected' : '' }}>Plastik</option>
                        <option value="Kertas" {{ old('kategori', $jenisSampah->kategori) === 'Kertas' ? 'selected' : '' }}>Kertas</option>
                        <option value="Logam" {{ old('kategori', $jenisSampah->kategori) === 'Logam' ? 'selected' : '' }}>Logam</option>
                        <option value="Kaca" {{ old('kategori', $jenisSampah->kategori) === 'Kaca' ? 'selected' : '' }}>Kaca</option>
                        <option value="Organik" {{ old('kategori', $jenisSampah->kategori) === 'Organik' ? 'selected' : '' }}>Organik</option>
                        <option value="Elektronik" {{ old('kategori', $jenisSampah->kategori) === 'Elektronik' ? 'selected' : '' }}>Elektronik</option>
                        <option value="Anorganik" {{ old('kategori', $jenisSampah->kategori) === 'Anorganik' ? 'selected' : '' }}>Anorganik</option>
                        <option value="Lainnya" {{ old('kategori', $jenisSampah->kategori) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label class="form-label">Harga Asli/kg (Rp) <span style="color:red">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">💰</span>
                        <input type="number" class="form-control" name="harga_asli_kg" required min="0" step="100" placeholder="Harga internal/laporan" value="{{ old('harga_asli_kg', $jenisSampah->harga_asli_kg) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga ke Warga/kg (Rp) <span style="color:red">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">👥</span>
                        <input type="number" class="form-control" name="harga_warga_kg" required min="0" step="100" placeholder="Harga yang ditampilkan ke nasabah" value="{{ old('harga_warga_kg', $jenisSampah->harga_warga_kg) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Poin/kg <span style="color:red">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">⭐</span>
                        <input type="number" class="form-control" name="poin_kg" required min="1" value="{{ old('poin_kg', $jenisSampah->poin_kg) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', $jenisSampah->aktif) ? 'checked' : '' }}>
                    Aktif
                </label>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('jenis-sampah.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
@endsection