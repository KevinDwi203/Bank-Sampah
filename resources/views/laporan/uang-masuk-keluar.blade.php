@extends('layouts.app')

@section('title', 'Laporan Uang Masuk/Keluar')
@section('pageTitle', 'Laporan Uang Masuk/Keluar')

@section('content')
    <!-- Filter Periode -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📅</span> Filter Periode</div>
        </div>
        <div style="padding: 16px 24px; display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Bulan</label>
                <select class="form-control" id="filterBulan" onchange="applyFilterUMK()">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tahun</label>
                <select class="form-control" id="filterTahun" onchange="applyFilterUMK()">
                    @for ($i = now()->year; $i >= now()->year - 3; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <a href="{{ route('laporan.pdf.uang-masuk-keluar', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline" target="_blank">📄 PDF</a>
        </div>
    </div>

    <!-- Ringkasan -->
    @php
        $totalMasuk = collect($data)->sum('masuk');
        $totalKeluar = collect($data)->sum('keluar');
        $saldoAkhir = collect($data)->last()['saldo'] ?? 0;
    @endphp
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">📥</div>
            <div class="stat-info"><h3>{{ formatRupiah($totalMasuk) }}</h3><p>Total Masuk</p></div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red">📤</div>
            <div class="stat-info"><h3>{{ formatRupiah($totalKeluar) }}</h3><p>Total Keluar</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💰</div>
            <div class="stat-info"><h3>{{ formatRupiah($saldoAkhir) }}</h3><p>Saldo Akhir</p></div>
        </div>
    </div>

    <!-- Tabel Uang Masuk/Keluar -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>💸</span> Detail Arus Kas</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Jenis</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ formatDate($item['tanggal']) }}</td>
                            <td>{{ $item['keterangan'] }}</td>
                            <td>
                                <span class="badge-status {{ 
                                    $item['jenis'] === 'setoran' ? 'success' : 
                                    ($item['jenis'] === 'penarikan' ? 'warning' : 'info') 
                                }}">
                                    {{ ucfirst($item['jenis']) }}
                                </span>
                            </td>
                            <td>{{ $item['masuk'] > 0 ? formatRupiah($item['masuk']) : '-' }}</td>
                            <td>{{ $item['keluar'] > 0 ? formatRupiah($item['keluar']) : '-' }}</td>
                            <td><strong>{{ formatRupiah($item['saldo']) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if (empty($data))
        <div class="card" style="text-align: center; padding: 60px;">
            <p style="color: var(--text-muted); font-size: 16px;">Tidak ada data arus kas pada periode ini</p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function applyFilterUMK() {
        const bulan = document.getElementById('filterBulan').value;
        const tahun = document.getElementById('filterTahun').value;
        window.location.href = '{{ route('laporan.uang-masuk-keluar') }}?bulan=' + bulan + '&tahun=' + tahun;
    }
</script>
@endpush