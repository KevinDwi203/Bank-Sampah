@extends('layouts.app')

@section('title', 'Tambah Nasabah')
@section('pageTitle', 'Tambah Nasabah')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Tambah Nasabah Baru</div>
        </div>

        <form action="{{ route('nasabah.store') }}" method="POST">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">No. Kartu</label>
                    <input type="text" class="form-control" value="{{ $nextKode }}" readonly style="background:#f5f5f5;">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">👤</span>
                    <input type="text" class="form-control" name="nama" placeholder="Masukkan nama lengkap" required value="{{ old('nama') }}">
                </div>
                @error('nama')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">📱</span>
                    <input type="tel" class="form-control" name="telepon" placeholder="08xx-xxxx-xxxx" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/\D/g, '')" required value="{{ old('telepon') }}">
                </div>
                @error('telepon')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="2" placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Saldo Awal (Rp)</label>
                    <div class="input-group">
                        <span class="input-icon">💰</span>
                        <input type="number" class="form-control" name="saldo" value="{{ old('saldo', 0) }}" min="0" step="1000">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Poin Awal</label>
                    <div class="input-group">
                        <span class="input-icon">⭐</span>
                        <input type="number" class="form-control" name="poin" value="{{ old('poin', 0) }}" min="0">
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