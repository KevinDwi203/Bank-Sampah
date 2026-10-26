@extends('layouts.app')

@section('title', 'Tambah User')
@section('pageTitle', 'Tambah User')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Tambah User Baru</div>
        </div>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Username <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="username" required placeholder="Username unik" value="{{ old('username') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span style="color:red">*</span></label>
                    <select class="form-control" name="role" required>
                        <option value="Administrator" {{ old('role') === 'Administrator' ? 'selected' : '' }}>Administrator</option>
                        <option value="Petugas" {{ old('role') === 'Petugas' ? 'selected' : '' }}>Petugas</option>
                        <option value="Nasabah" {{ old('role') === 'Nasabah' ? 'selected' : '' }}>Nasabah</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="nama" required placeholder="Nama lengkap" value="{{ old('nama') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Password <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" class="form-control" name="password" required placeholder="Minimal 6 karakter" minlength="6">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">🔒</span>
                    <input type="password" class="form-control" name="password_confirmation" required placeholder="Ulangi password">
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="aktif" value="1" checked>
                    Aktif
                </label>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
@endsection