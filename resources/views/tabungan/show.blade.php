@extends('layouts.app')

@section('title', 'Detail Tabungan')
@section('pageTitle', 'Detail Tabungan - {{ $nasabah->nama }}')

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> {{ $nasabah->nama }} ({{ $nasabah->kode }})</div>
            <a href="{{ route('tabungan.index') }}" class="btn btn-outline btn-sm">← Kembali</a>
        </div>

        <div style="padding: 24px;">
            <div class="grid-3" style="margin-bottom: 24px;">
                <div class="stat-card green">
                    <div class="stat-icon green">💰</div>
                    <div class="stat-info"><h3>{{ formatRupiah($nasabah->saldo) }}</h3><p>Saldo Saat Ini</p></div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon blue">📥</div>
                    <div class="stat-info"><h3>{{ formatRupiah($nasabah->total_setoran ?? 0) }}</h3><p>Total Setoran</p></div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon red">📤</div>
                    <div class="stat-info"><h3>{{ formatRupiah($nasabah->total_penarikan ?? 0) }}</h3><p>Total Penarikan</p></div>
                </div>
            </div>

            <!-- Transaksi Setoran -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <div class="card-title"><span>📦</span> Riwayat Setoran</div>
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
                                <th>Poin</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksi as $tr)
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
                                    <td>{{ $tr->poin_dapat }} poin</td>
                                    <td><span class="badge-status {{ $tr->status === 'Selesai' ? 'success' : ($tr->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $tr->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada setoran</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    {{ $transaksi->links() }}
                </div>
            </div>

            <!-- Penarikan -->
            <div class="card">
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
                            @forelse ($penarikan as $p)
                                <tr>
                                    <td><strong>{{ $p->kode }}</strong></td>
                                    <td>{{ formatDate($p->tanggal) }}</td>
                                    <td><strong>{{ formatRupiah($p->jumlah) }}</strong></td>
                                    <td>{{ $p->metode }}</td>
                                    <td><span class="badge-status {{ $p->status === 'Selesai' ? 'success' : ($p->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $p->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada penarikan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    {{ $penarikan->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection