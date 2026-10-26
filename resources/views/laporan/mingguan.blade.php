@extends('layouts.app')

@section('title', 'Laporan Mingguan')
@section('pageTitle', 'Laporan Mingguan')

@section('content')
    <!-- Filter Periode -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📅</span> Filter Periode</div>
        </div>
        <div style="padding: 16px 24px; display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" id="filterDari" value="{{ $dari }}">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" id="filterSampai" value="{{ $sampai }}">
            </div>
            <button class="btn btn-primary" onclick="applyFilterMingguan()">Terapkan</button>
            <a href="{{ route('laporan.pdf.rekap', ['dari' => $dari, 'sampai' => $sampai]) }}" class="btn btn-outline" target="_blank">📄 PDF</a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info"><h3>{{ number_format(collect($data)->sum('total_kg'), 1) }} kg</h3><p>Total Berat</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info"><h3>{{ formatRupiah(collect($data)->sum('total_nilai')) }}</h3><p>Total Nilai</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">📋</div>
            <div class="stat-info"><h3>{{ collect($data)->sum(fn($d) => count($d['items'])) }}</h3><p>Total Transaksi</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple">👥</div>
            <div class="stat-info"><h3>{{ count($data) }}</h3><p>Jumlah Setoran</p></div>
        </div>
    </div>

    <!-- Tabel Rekap Mingguan -->
    @foreach ($data as $rekap)
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title"><span>📅</span> {{ formatDate($rekap['tanggal']) }} - {{ $rekap['nasabah']->nama }} ({{ $rekap['nasabah']->kode }})</div>
                <div style="font-size:13px; color:var(--text-muted);">
                    Total: {{ number_format($rekap['total_kg'], 2) }} kg | {{ formatRupiah($rekap['total_nilai']) }}
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Berat (kg)</th>
                            <th>Harga/kg</th>
                            <th>Total</th>
                            <th>Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap['items'] as $item)
                            <tr>
                                <td>
                                    <span class="trash-tag {{ strtolower(str_replace(' ', '-', $item->jenisSampah->kategori ?? 'lainnya')) }}">
                                        {{ $item->jenisSampah->ikon ?? '♻️' }} {{ $item->jenisSampah->nama }}
                                    </span>
                                </td>
                                <td>{{ number_format($item->berat_kg, 2) }}</td>
                                <td>{{ formatRupiah($item->harga_warga_kg) }}</td>
                                <td><strong>{{ formatRupiah($item->total) }}</strong></td>
                                <td>{{ $item->poin_dapat }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; background: #f9f9f9;">
                            <td>TOTAL</td>
                            <td>{{ number_format($rekap['total_kg'], 2) }}</td>
                            <td></td>
                            <td>{{ formatRupiah($rekap['total_nilai']) }}</td>
                            <td>{{ collect($rekap['items'])->sum('poin_dapat') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if (empty($data))
        <div class="card" style="text-align: center; padding: 60px;">
            <p style="color: var(--text-muted); font-size: 16px;">Tidak ada data setoran pada periode ini</p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function applyFilterMingguan() {
        const dari = document.getElementById('filterDari').value;
        const sampai = document.getElementById('filterSampai').value;
        window.location.href = '{{ route('laporan.mingguan') }}?dari=' + dari + '&sampai=' + sampai;
    }
</script>
@endpush