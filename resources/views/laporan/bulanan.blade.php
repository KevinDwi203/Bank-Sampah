@extends('layouts.app')

@section('title', 'Laporan Bulanan Detail')
@section('pageTitle', 'Laporan Bulanan Detail')

@section('content')
    <!-- Filter Periode -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📅</span> Filter Periode</div>
        </div>
        <div style="padding: 16px 24px; display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Bulan</label>
                <select class="form-control" id="filterBulan" onchange="applyFilterBulanan()">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tahun</label>
                <select class="form-control" id="filterTahun" onchange="applyFilterBulanan()">
                    @for ($i = now()->year; $i >= now()->year - 3; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <a href="{{ route('laporan.pdf.rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline" target="_blank">📄 PDF</a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info"><h3>{{ number_format($data['ringkasan']['total_kg'], 1) }} kg</h3><p>Total Berat</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info"><h3>{{ formatRupiah($data['ringkasan']['total_nilai']) }}</h3><p>Total Nilai</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">📋</div>
            <div class="stat-info"><h3>{{ $data['ringkasan']['total_transaksi'] }}</h3><p>Total Transaksi</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple">👥</div>
            <div class="stat-info"><h3>{{ $data['ringkasan']['nasabah_aktif'] }}</h3><p>Nasabah Aktif</p></div>
        </div>
    </div>

    <!-- Per Nasabah per Minggu -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📊</span> Rekap per Nasabah per Minggu</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No. Kartu</th>
                        <th>Nama</th>
                        @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                            <th>Minggu {{ $i + 1 }}<br><small>{{ formatShortDate($week[0]) }} - {{ formatShortDate($week[1]) }}</small></th>
                        @endforeach
                        <th>Total Berat</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['per_nasabah']['data'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item['nasabah']->kode }}</strong></td>
                            <td>{{ $item['nasabah']->nama }}</td>
                            @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                                <td>
                                    {{ number_format($item['minggu'][$i + 1]['kg'] ?? 0, 1) }} kg
                                    <br><small>{{ formatRupiah($item['minggu'][$i + 1]['nilai'] ?? 0) }}</small>
                                </td>
                            @endforeach
                            <td><strong>{{ number_format($item['total_kg'], 1) }} kg</strong></td>
                            <td><strong>{{ formatRupiah($item['total_nilai']) }}</strong></td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #f0f0f0;">
                        <td colspan="3">TOTAL</td>
                        @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                            <td>
                                {{ number_format(collect($data['per_nasabah']['data'])->sum('minggu.' . ($i + 1) . '.kg'), 1) }} kg
                            </td>
                        @endforeach
                        <td>{{ number_format(collect($data['per_nasabah']['data'])->sum('total_kg'), 1) }} kg</td>
                        <td>{{ formatRupiah(collect($data['per_nasabah']['data'])->sum('total_nilai')) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Per Jenis Sampah -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>🏷️</span> Rekap per Jenis Sampah</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ikon</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th>Total Berat (kg)</th>
                        <th>Total Nilai</th>
                        <th>Jumlah Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['per_jenis'] as $item)
                        <tr>
                            <td style="font-size:20px;">{{ $item['ikon'] ?? '♻️' }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td><span class="trash-tag {{ strtolower($item['kategori']) }}">{{ $item['kategori'] }}</span></td>
                            <td><strong>{{ number_format($item['total_kg'], 1) }}</strong></td>
                            <td><strong>{{ formatRupiah($item['total_nilai']) }}</strong></td>
                            <td>{{ $item['jumlah_transaksi'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilterBulanan() {
        const bulan = document.getElementById('filterBulan').value;
        const tahun = document.getElementById('filterTahun').value;
        window.location.href = '{{ route('laporan.bulanan') }}?bulan=' + bulan + '&tahun=' + tahun;
    }
</script>
@endpush