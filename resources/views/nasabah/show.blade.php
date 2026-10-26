@extends('layouts.app')

@section('title', 'Detail Nasabah')
@section('pageTitle', 'Detail Nasabah')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>🔍</span> Profil Nasabah</div>
        </div>

        <div style="padding: 24px;">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:70px; height:70px; background:#e8f5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 12px;">👤</div>
                <h3 style="font-size:20px;">{{ $nasabah->nama }}</h3>
                <span class="badge-status {{ $nasabah->status === 'Aktif' ? 'success' : 'danger' }}" style="margin-top:6px;">● {{ $nasabah->status }}</span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px 20px; background:#f9f9f9; padding:16px; border-radius:10px; font-size:13.5px;">
                <div><span style="color:var(--text-muted); font-size:11px;">ID NASABAH</span><br><strong>{{ $nasabah->kode }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TELEPON</span><br><strong>{{ $nasabah->telepon ?? '—' }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">SALDO TABUNGAN</span><br><strong style="color:var(--primary); font-size:16px;">{{ formatRupiah($nasabah->saldo) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">POIN</span><br><strong>{{ $nasabah->poin }} poin</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">USERNAME</span><br><strong>{{ $nasabah->user->username ?? '—' }}</strong></div>
                <div style="grid-column:span 2;"><span style="color:var(--text-muted); font-size:11px;">ALAMAT</span><br><strong>{{ $nasabah->alamat ?? '—' }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TOTAL TRANSAKSI</span><br><strong>{{ $transaksiCount }} kali</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TOTAL SETORAN</span><br><strong style="color:var(--primary);">{{ formatRupiah($transaksiTotal) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">SETORAN TERAKHIR</span><br><strong>{{ $nasabah->last_deposit_at ? formatDate($nasabah->last_deposit_at) : '—' }}</strong></div>
            </div>
            
            <div style="margin-top: 24px;">
                <a href="{{ route('nasabah.index') }}" class="btn btn-outline">← Kembali</a>
                <a href="{{ route('nasabah.edit', $nasabah) }}" class="btn btn-primary" style="margin-left: 8px;">✏️ Edit</a>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📋</span> Riwayat Transaksi</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Jenis Sampah</th>
                        <th>Berat</th>
                        <th>Harga/kg</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nasabah->transaksiSelesai()->latest('tanggal')->take(20)->get() as $tr)
                        <tr>
                            <td><strong>{{ $tr->kode }}</strong></td>
                            <td>{{ formatDate($tr->tanggal) }}</td>
                            <td>
                                <span class="trash-tag {{ strtolower(str_replace(' ', '-', $tr->jenisSampah->kategori ?? 'lainnya')) }}">
                                    {{ $tr->jenisSampah->ikon ?? '♻️' }} {{ $tr->jenisSampah->nama }}
                                </span>
                            </td>
                            <td>{{ number_format($tr->berat_kg, 2) }} kg</td>
                            <td>{{ formatRupiah($tr->harga_warga_kg) }}</td>
                            <td><strong>{{ formatRupiah($tr->total) }}</strong></td>
                            <td><span class="badge-status {{ $tr->status === 'Selesai' ? 'success' : ($tr->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $tr->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada transaksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Riwayat Penarikan -->
    <div class="card" style="margin-top: 24px;">
        <div class="card-header">
            <div class="card-title"><span>🏧</span> Riwayat Penarikan</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nasabah->penarikanSelesai()->latest('tanggal')->take(10)->get() as $wd)
                        <tr>
                            <td><strong>{{ $wd->kode }}</strong></td>
                            <td>{{ formatDate($wd->tanggal) }}</td>
                            <td><strong>{{ formatRupiah($wd->jumlah) }}</strong></td>
                            <td>{{ $wd->metode }}</td>
                            <td><span class="badge-status {{ $wd->status === 'Selesai' ? 'success' : ($wd->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $wd->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada penarikan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection