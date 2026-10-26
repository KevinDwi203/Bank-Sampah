@extends('layouts.app')

@section('title', 'Edit User')
@section('pageTitle', 'Edit User')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Edit User: {{ $user->username }}</div>
        </div>

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Username <span style="color:red">*</span></label>
                    <input type="text" class="form-control" name="username" required value="{{ old('username', $user->username) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span style="color:red">*</span></label>
                    <select class="form-control" name="role" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach (['Administrator', 'Petugas', 'Nasabah'] as $r)
                            <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="nama" required value="{{ old('nama', $user->nama) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru <small style="color:var(--text-muted)">(kosongkan jika tidak diganti)</small></label>
                <input type="password" class="form-control" name="password" minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="checkbox-wrap">
                    <input type="checkbox" name="aktif" value="1" {{ old('aktif', $user->aktif) ? 'checked' : '' }} {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                    Aktif
                </label>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
