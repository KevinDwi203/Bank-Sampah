@extends('layouts.app')

@section('title', 'Laporan')
@section('pageTitle', 'Laporan')

@section('content')
    <!-- Filter Periode -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📅</span> Filter Periode</div>
        </div>
        <div style="padding: 16px 24px; display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Bulan</label>
                <select class="form-control" id="filterBulan" onchange="updateLaporan()">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tahun</label>
                <select class="form-control" id="filterTahun" onchange="updateLaporan()">
                    @for ($i = now()->year; $i >= now()->year - 3; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('laporan.mingguan') }}" class="btn btn-outline">📅 Mingguan</a>
                <a href="{{ route('laporan.bulanan') }}" class="btn btn-outline">📆 Bulanan Detail</a>
                <a href="{{ route('laporan.kiloan') }}" class="btn btn-outline">📦 Kiloan</a>
            </div>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info"><h3>{{ number_format($laporan['total_kg'], 1) }} kg</h3><p>Total Sampah</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info"><h3>{{ formatRupiah($laporan['total_nilai']) }}</h3><p>Total Nilai (Harga Warga)</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">📋</div>
            <div class="stat-info"><h3>{{ $laporan['total_transaksi'] }}</h3><p>Total Transaksi</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple">👥</div>
            <div class="stat-info"><h3>{{ $laporan['nasabah_aktif'] }}</h3><p>Nasabah Aktif</p></div>
        </div>
    </div>

    <!-- Rekap Sampah per Jenis (Harga Asli) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>🏷️</span> Rekap Sampah per Jenis (Harga Asli)</div>
            <a href="{{ route('laporan.pdf.rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-primary" target="_blank">📄 Unduh PDF</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Sampah</th>
                        <th>Total Berat (Kg)</th>
                        <th>Harga Asli/Kg</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap['items'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['nama'] }}</td>
                            <td>{{ number_format($item['total_kg'], 2, ',', '.') }}</td>
                            <td>{{ formatRupiah(round($item['harga_asli_kg'])) }}</td>
                            <td><strong>{{ formatRupiah($item['total_nilai']) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada setoran pada periode ini</td>
                        </tr>
                    @endforelse
                    @if (count($rekap['items']) > 0)
                        <tr>
                            <td colspan="2"><strong>Total Keseluruhan</strong></td>
                            <td><strong>{{ number_format($rekap['total_kg'], 2, ',', '.') }}</strong></td>
                            <td>-</td>
                            <td><strong>{{ formatRupiah($rekap['total_nilai']) }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function updateLaporan() {
        const bulan = document.getElementById('filterBulan').value;
        const tahun = document.getElementById('filterTahun').value;
        window.location.href = '{{ route('laporan.index') }}?bulan=' + bulan + '&tahun=' + tahun;
    }
</script>
@endpush