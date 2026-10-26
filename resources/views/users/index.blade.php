@extends('layouts.app')

@section('title', 'Kelola User')
@section('pageTitle', 'Kelola User')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Daftar User</div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">＋ Tambah User</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><strong>{{ $user->username }}</strong></td>
                            <td>{{ $user->nama }}</td>
                            <td>
                                <span class="badge-status {{ 
                                    $user->role === 'Administrator' ? 'success' : 
                                    ($user->role === 'Petugas' ? 'info' : 'warning') 
                                }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-status {{ $user->aktif ? 'success' : 'danger' }}">
                                    ● {{ $user->aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn-icon" title="Edit">✏️</a>
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon" title="Hapus" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size:12px;">(Akun Anda)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada user</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection