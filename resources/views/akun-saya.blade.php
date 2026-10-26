@extends('layouts.app')

@section('title', 'Dashboard')
@section('pageTitle', 'Dashboard')

@section('content')
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card green">
            <div class="stat-icon green">💰</div>
            <div class="stat-info">
                <h3>{{ formatRupiah($nasabah->saldo) }}</h3>
                <p>Saldo Tabungan Saya</p>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">♻️</div>
            <div class="stat-info">
                <h3>{{ number_format($totalKg, 2, ',', '.') }} kg</h3>
                <p>Total Sampah Disetor</p>
            </div>
        </div>
    </div>

    <!-- Data diri -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>👤</span> Data Diri</div>
            <a href="{{ route('profile') }}" class="btn btn-outline btn-sm">Ubah Profil</a>
        </div>
        <div class="table-wrapper">
            <table>
                <tbody>
                    <tr><td><strong>No. Kartu</strong></td><td>{{ $nasabah->nomor_kartu ?? $nasabah->kode }}</td></tr>
                    <tr><td><strong>Nama</strong></td><td>{{ $nasabah->nama }}</td></tr>
                    <tr><td><strong>Telepon</strong></td><td>{{ $nasabah->telepon ?: '—' }}</td></tr>
                    <tr><td><strong>Alamat</strong></td><td>{{ $nasabah->alamat ?: '—' }}</td></tr>
                    <tr><td><strong>Setoran Terakhir</strong></td><td>{{ $nasabah->last_deposit_at ? $nasabah->last_deposit_at->format('d/m/Y') : '—' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sampah yang sudah disetor -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📦</span> Sampah yang Sudah Saya Setor</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Sampah</th>
                        <th>Total Berat (Kg)</th>
                        <th>Harga Warga/Kg</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($setoran as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $s['ikon'] ?? '♻️' }} {{ $s['nama'] }}</td>
                            <td>{{ number_format($s['total_kg'], 2, ',', '.') }}</td>
                            <td>{{ $s['harga_kg'] !== null ? formatRupiah(round($s['harga_kg'])) : '-' }}</td>
                            <td><strong>{{ formatRupiah($s['total_nilai']) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada setoran</td>
                        </tr>
                    @endforelse
                    @if ($setoran->count() > 0)
                        <tr>
                            <td colspan="2"><strong>Total Keseluruhan</strong></td>
                            <td><strong>{{ number_format($totalKg, 2, ',', '.') }}</strong></td>
                            <td>-</td>
                            <td><strong>{{ formatRupiah($totalNilai) }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Harga sampah warga -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>🏷️</span> Harga Sampah Warga per Kilogram</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Jenis Sampah</th>
                        <th>Harga Warga/Kg</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hargaWarga as $j)
                        <tr>
                            <td>{{ $j->ikon ?? '♻️' }} {{ $j->nama }}</td>
                            <td><strong>{{ formatRupiah($j->harga_warga_kg) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center; padding:40px; color:var(--text-muted);">Belum ada data harga</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
