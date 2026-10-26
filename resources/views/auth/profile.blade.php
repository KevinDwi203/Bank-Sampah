@extends('layouts.app')

@section('title', 'Profil Saya')
@section('pageTitle', 'Profil Saya')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Profil Saya</div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="{{ $user->username }}" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="nama" required value="{{ old('nama', $user->nama) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" maxlength="100" value="{{ old('email', $user->email) }}" placeholder="contoh@email.com">
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" class="form-control" name="telepon" maxlength="20" value="{{ old('telepon', $user->telepon) }}" placeholder="08xxxxxxxxxx">
            </div>

            <div class="form-group">
                <label class="form-label">Password Baru <small style="color:var(--text-muted)">(kosongkan jika tidak diganti)</small></label>
                <input type="password" class="form-control" name="password" minlength="6" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ $user->homeRoute() }}" class="btn btn-outline">Kembali</a>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
@endsection
