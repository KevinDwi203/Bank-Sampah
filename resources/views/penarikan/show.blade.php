@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('pageTitle', 'Detail Transaksi')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>🔍</span> Detail Transaksi</div>
        </div>

        <div style="padding: 24px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px 20px; background:#f9f9f9; padding:16px; border-radius:10px;">
                <div><span style="color:var(--text-muted); font-size:12px;">Kode</span><br><strong>{{ $penarikan->kode }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Nasabah</span><br><strong>{{ $penarikan->nasabah->nama }} ({{ $penarikan->nasabah->kode }})</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Jumlah</span><br><strong style="color:var(--primary); font-size:16px;">{{ formatRupiah($penarikan->jumlah) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Metode</span><br><strong>{{ $penarikan->metode }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Tanggal</span><br><strong>{{ formatDate($penarikan->tanggal) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Status</span><br>
                    <span class="badge-status {{ $penarikan->status === 'Selesai' ? 'success' : ($penarikan->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $penarikan->status }}</span>
                </div>
                @if ($penarikan->catatan)
                    <div style="grid-column:span 2;"><span style="color:var(--text-muted); font-size:12px;">Catatan</span><br>{{ $penarikan->catatan }}</div>
                @endif
            </div>

            <div style="margin-top: 24px; display: flex; gap: 8px;">
                <a href="{{ route('penarikan.index') }}" class="btn btn-outline">← Kembali</a>
                
                <form action="{{ route('penarikan.updateStatus', $penarikan) }}" method="POST" style="display:inline;">
                    @csrf @method('PUT')
                    <select name="status" class="form-control" style="width:auto; display:inline-block;" onchange="this.form.submit()">
                        <option value="Selesai" {{ $penarikan->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Pending" {{ $penarikan->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Dibatalkan" {{ $penarikan->status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
                
                @if ($penarikan->status !== 'Selesai')
                    <form action="{{ route('penarikan.destroy', $penarikan) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus penarikan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">🗑️ Hapus</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection