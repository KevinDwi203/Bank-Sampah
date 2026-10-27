@extends('layouts.app')

@section('title', 'Laporan')
@section('pageTitle', 'Laporan')

@section('content')
    <!-- Filter Periode & Aksi -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <div class="card-title"><span>📅</span> Filter Periode &amp; Laporan</div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('laporan.create') }}" class="btn btn-primary">➕ Input Laporan Baru</a>
                <a href="{{ route('laporan.pdf.rekap', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline" target="_blank">📄 Unduh PDF</a>
            </div>
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
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info">
                <h3>{{ number_format($totalKg, 2, ',', '.') }} kg</h3>
                <p>Total Berat Sampah</p>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info">
                <h3>{{ formatRupiah($totalNilai) }}</h3>
                <p>Total Nilai Laporan</p>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">📋</div>
            <div class="stat-info">
                <h3>{{ $totalLaporan }}</h3>
                <p>Total Laporan Diinput</p>
            </div>
        </div>
    </div>

    <!-- TABEL UTAMA LAPORAN MANUAL -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📝</span> Daftar Laporan Manual</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Laporan</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Rincian Jenis Sampah</th>
                        <th>Total Berat</th>
                        <th>Total Nilai</th>
                        <th>Petugas</th>
                        <th style="width: 110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporans as $index => $lap)
                        <tr>
                            <td>{{ $laporans->firstItem() + $index }}</td>
                            <td><strong>{{ $lap->kode }}</strong></td>
                            <td>{{ formatDate($lap->tanggal) }}</td>
                            <td>{{ $lap->keterangan }}</td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach ($lap->items as $it)
                                        <div style="font-size: 12.5px;">
                                            <span style="font-weight: 600; color: #15803d;">{{ $it->nama_jenis }}</span>: 
                                            {{ number_format($it->berat_kg, 2, ',', '.') }} kg 
                                            <span style="color: var(--text-muted); font-size: 11.5px;">({{ formatRupiah($it->subtotal) }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td><strong style="color: #15803d;">{{ number_format($lap->total_berat_kg, 2, ',', '.') }} kg</strong></td>
                            <td><strong style="color: var(--primary);">{{ formatRupiah($lap->total_nilai) }}</strong></td>
                            <td>{{ $lap->user->nama ?? '-' }}</td>
                            <td>
                                <a href="{{ route('laporan.show', $lap) }}" class="btn-icon" title="Detail">👁️</a>
                                <a href="{{ route('laporan.edit', $lap) }}" class="btn-icon" title="Edit" style="margin-left: 6px;">✏️</a>
                                <form action="{{ route('laporan.destroy', $lap) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus laporan {{ $lap->kode }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus" style="margin-left: 6px; background: none; border: none; cursor: pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                Belum ada data laporan manual untuk periode ini.<br>
                                <a href="{{ route('laporan.create') }}" class="btn btn-primary" style="margin-top: 12px; display: inline-block;">➕ Input Laporan Baru</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($laporans->hasPages())
            <div class="pagination" style="padding: 16px;">
                {{ $laporans->links() }}
            </div>
        @endif
    </div>

    <!-- REKAPITULASI PER JENIS SAMPAH -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>🏷️</span> Rekapitulasi per Jenis Sampah (Laporan Manual)</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Jenis Sampah</th>
                        <th>Total Berat (Kg)</th>
                        <th>Rata-rata Harga / Kg</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapPerJenis as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item['nama'] }}</strong></td>
                            <td>{{ number_format($item['total_kg'], 2, ',', '.') }} kg</td>
                            <td>{{ formatRupiah(round($item['avg_harga'])) }}</td>
                            <td><strong style="color: var(--primary);">{{ formatRupiah($item['total_nilai']) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Belum ada rekapitulasi data sampah
                            </td>
                        </tr>
                    @endforelse
                    @if (count($rekapPerJenis) > 0)
                        <tr style="font-weight: bold; background: #f8fafc;">
                            <td colspan="2">TOTAL KESELURUHAN</td>
                            <td>{{ number_format($totalKg, 2, ',', '.') }} kg</td>
                            <td>—</td>
                            <td style="color: var(--primary);">{{ formatRupiah($totalNilai) }}</td>
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
