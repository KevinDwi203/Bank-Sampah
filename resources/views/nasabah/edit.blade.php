@extends('layouts.app')

@section('title', 'Edit Nasabah')
@section('pageTitle', 'Edit Nasabah')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>✏️</span> Edit Nasabah</div>
        </div>

        <form action="{{ route('nasabah.update', $nasabah) }}" method="POST">
            @csrf @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">No. Kartu</label>
                    <input type="text" class="form-control" value="{{ $nasabah->kode }}" readonly style="background:#f5f5f5;">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="Aktif" {{ $nasabah->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ $nasabah->status === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">👤</span>
                    <input type="text" class="form-control" name="nama" placeholder="Masukkan nama lengkap" required value="{{ old('nama', $nasabah->nama) }}">
                </div>
                @error('nama')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">📱</span>
                    <input type="tel" class="form-control" name="telepon" placeholder="08xx-xxxx-xxxx" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/\D/g, '')" required value="{{ old('telepon', $nasabah->telepon) }}">
                </div>
                @error('telepon')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap">{{ old('alamat', $nasabah->alamat) }}</textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Saldo (Rp)</label>
                    <div class="input-group">
                        <span class="input-icon">💰</span>
                        <input type="number" class="form-control" name="saldo" value="{{ old('saldo', $nasabah->saldo) }}" min="0" step="1000">
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('nasabah.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
@endsection