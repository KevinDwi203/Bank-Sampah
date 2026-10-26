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
                <div><span style="color:var(--text-muted); font-size:12px;">ID Transaksi</span><br><strong>{{ $transaksi->kode }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Nasabah</span><br><strong>{{ $transaksi->nasabah->nama }} ({{ $transaksi->nasabah->kode }})</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Jenis Sampah</span><br>
                    <span class="trash-tag {{ strtolower(str_replace(' ', '-', $transaksi->jenisSampah->kategori ?? 'lainnya')) }}">
                        {{ $transaksi->jenisSampah->ikon ?? '♻️' }} {{ $transaksi->jenisSampah->nama }}
                    </span>
                </div>
                <div><span style="color:var(--text-muted); font-size:12px;">Berat</span><br><strong>{{ number_format($transaksi->berat_kg, 2) }} kg</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Harga/kg (Warga)</span><br><strong>{{ formatRupiah($transaksi->harga_warga_kg) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Harga/kg (Asli)</span><br><strong>{{ formatRupiah($transaksi->harga_asli_kg) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Total</span><br>
                    <strong style="color:var(--primary); font-size:16px;">{{ formatRupiah($transaksi->total) }}</strong>
                </div>
                <div><span style="color:var(--text-muted); font-size:12px;">Poin Didapat</span><br><strong>{{ $transaksi->poin_dapat }} poin</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Tanggal</span><br><strong>{{ formatDate($transaksi->tanggal) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:12px;">Status</span><br>
                    <span class="badge-status {{ $transaksi->status === 'Selesai' ? 'success' : ($transaksi->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $transaksi->status }}</span>
                </div>
                @if ($transaksi->catatan)
                    <div style="grid-column:span 2;"><span style="color:var(--text-muted); font-size:12px;">Catatan</span><br>{{ $transaksi->catatan }}</div>
                @endif
            </div>

            <div style="margin-top: 24px; display: flex; gap: 8px;">
                <a href="{{ route('riwayat.index') }}" class="btn btn-outline">← Kembali</a>
                
                <form action="{{ route('riwayat.updateStatus', $transaksi) }}" method="POST" style="display:inline;">
                    @csrf @method('PUT')
                    <select name="status" class="form-control" style="width:auto; display:inline-block;" onchange="this.form.submit()">
                        <option value="Selesai" {{ $transaksi->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Pending" {{ $transaksi->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Dibatalkan" {{ $transaksi->status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </form>
                
                <form action="{{ route('riwayat.destroy', $transaksi) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus transaksi ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">🗑️ Hapus</button>
                </form>
            </div>
        </div>
    </div>
@endsection