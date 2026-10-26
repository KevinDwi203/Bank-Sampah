@extends('layouts.app')

@section('title', 'Laporan Setoran Kiloan')
@section('pageTitle', 'Laporan Setoran Kiloan')

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
            <button class="btn btn-primary" onclick="applyFilterKiloan()">Terapkan</button>
            <a href="{{ route('laporan.pdf.rekap', ['dari' => $dari, 'sampai' => $sampai]) }}" class="btn btn-outline" target="_blank">📄 PDF</a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info"><h3>{{ number_format(collect($data)->sum('berat_kg'), 1) }} kg</h3><p>Total Berat</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info"><h3>{{ formatRupiah(collect($data)->sum('jumlah_harga')) }}</h3><p>Total Nilai (Harga Warga)</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">💰</div>
            <div class="stat-info"><h3>{{ formatRupiah(collect($data)->sum('harga_asli_kg') > 0 ? collect($data)->sum(fn($d) => $d['harga_asli_kg'] * $d['berat_kg']) : 0) }}</h3><p>Total Nilai (Harga Asli)</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple">📋</div>
            <div class="stat-info"><h3>{{ count($data) }}</h3><p>Total Item</p></div>
        </div>
    </div>

    <!-- Tabel Kiloan -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>📦</span> Detail Setoran Kiloan</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Barang</th>
                        <th>Jenis Barang</th>
                        <th>Harga Warga/kg</th>
                        <th>Harga Asli/kg</th>
                        <th>Berat (kg)</th>
                        <th>Jumlah Harga (Warga)</th>
                        <th>Jumlah Harga (Asli)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ formatDate($item['tanggal']) }}</td>
                            <td><strong>{{ $item['kode'] ?? '-' }}</strong></td>
                            <td>{{ $item['nama'] }}</td>
                            <td>{{ formatRupiah($item['harga_kg']) }}</td>
                            <td>{{ formatRupiah($item['harga_asli_kg']) }}</td>
                            <td>{{ number_format($item['berat_kg'], 2) }}</td>
                            <td><strong>{{ formatRupiah($item['jumlah_harga']) }}</strong></td>
                            <td><strong>{{ formatRupiah($item['harga_asli_kg'] * $item['berat_kg']) }}</strong></td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #f0f0f0;">
                        <td colspan="5">TOTAL</td>
                        <td>{{ number_format(collect($data)->sum('berat_kg'), 2) }}</td>
                        <td>{{ formatRupiah(collect($data)->sum('jumlah_harga')) }}</td>
                        <td>{{ formatRupiah(collect($data)->sum(fn($d) => $d['harga_asli_kg'] * $d['berat_kg'])) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if (empty($data))
        <div class="card" style="text-align: center; padding: 60px;">
            <p style="color: var(--text-muted); font-size: 16px;">Tidak ada data setoran pada periode ini</p>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function applyFilterKiloan() {
        const dari = document.getElementById('filterDari').value;
        const sampai = document.getElementById('filterSampai').value;
        window.location.href = '{{ route('laporan.kiloan') }}?dari=' + dari + '&sampai=' + sampai;
    }
</script>
@endpush