@extends('layouts.app')

@section('title', 'Setor Sampah')
@section('pageTitle', 'Setor Sampah')

@section('content')
    <!-- STAT MINI -->
    <div class="grid-3" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">📦</div>
            <div class="stat-info">
                <h3>{{ $stats['setoran_hari_ini'] }}</h3>
                <p>Setoran Hari Ini</p>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">⚖️</div>
            <div class="stat-info">
                <h3>{{ number_format($stats['berat_hari_ini'], 1) }} kg</h3>
                <p>Berat Hari Ini</p>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💵</div>
            <div class="stat-info">
                <h3>{{ formatRupiah($stats['nilai_hari_ini']) }}</h3>
                <p>Nilai Hari Ini</p>
            </div>
        </div>
    </div>

    <!-- DAFTAR SETORAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>📋</span> Daftar Setoran</div>
            <div style="display:flex; gap:10px;">
                <select class="form-control" style="width:auto; padding:8px 12px; font-size:13px;" id="filterJenis" onchange="applyFilter()">
                    <option value="">Semua Jenis</option>
                    @foreach ($jenisSampah as $j)
                        <option value="{{ $j->nama }}" {{ request('jenis') === $j->nama ? 'selected' : '' }}>{{ $j->nama }}</option>
                    @endforeach
                </select>
                <select class="form-control" style="width:auto; padding:8px 12px; font-size:13px;" id="filterStatus" onchange="applyFilter()">
                    <option value="">Semua Status</option>
                    <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Dibatalkan" {{ request('status') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchSetor" placeholder="Cari nama nasabah atau ID transaksi..." value="{{ request('q') }}" oninput="applyFilter()">
            </div>
            <a href="{{ route('riwayat.create') }}" class="btn btn-primary">＋ Transaksi Baru</a>
        </div>

        <div class="table-wrapper setor-rekap-wrapper">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Tgl.</th>
                        <th rowspan="2">No. Kartu</th>
                        <th rowspan="2">Nasabah</th>
                        @php
                            $kodeOrder = ['GP', 'BP', 'KD', 'KL', 'GB', 'BE1', 'BE2', 'DP', 'HVS', 'KB', 'BU', 'PK', 'AL'];
                        @endphp
                        <th colspan="{{ count($kodeOrder) }}">Jenis Sampah/Kg</th>
                        <th colspan="3">Lainnya</th>
                        <th rowspan="2">Total Berat</th>
                        <th rowspan="2">Jumlah Saldo</th>
                        <th rowspan="2">Aksi</th>
                    </tr>
                    <tr>
                        @foreach ($kodeOrder as $kode)
                            <th>{{ $kode }}</th>
                        @endforeach
                        <th>Jenis</th><th>Berat</th><th>Total Rp</th>
                    </tr>
                </thead>
                <tbody id="setorTable">
                    @forelse ($transaksi as $t)
                        @php
                            $jenisKode = $t->jenisSampah->kode ?? strtoupper(substr($t->jenisSampah->nama ?? '', 0, 2));
                            $kodeIdx = array_search($jenisKode, $kodeOrder);
                        @endphp
                        <tr>
                            <td><strong>{{ formatShortDate($t->tanggal) }}</strong></td>
                            <td><strong>{{ $t->nasabah->kode }}</strong></td>
                            <td>{{ $t->nasabah->nama }}</td>
                            @foreach ($kodeOrder as $kode)
                                <td>
                                    @if ($kodeIdx !== false && $kodeIdx === $loop->index)
                                        {{ number_format($t->berat_kg, 2) }} Kg
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if ($kodeIdx === false)
                                    {{ $t->jenisSampah->nama ?? 'Lainnya' }}
                                @endif
                            </td>
                            <td>
                                @if ($kodeIdx === false)
                                    {{ number_format($t->berat_kg, 2) }} Kg
                                @endif
                            </td>
                            <td>
                                @if ($kodeIdx === false)
                                    {{ formatRupiah($t->total) }}
                                @endif
                            </td>
                            <td><strong>{{ number_format($t->berat_kg, 2) }} Kg</strong></td>
                            <td><strong style="color:var(--primary);">{{ formatRupiah($t->total) }}</strong></td>
                            <td>
                                <a href="{{ route('riwayat.show', $t) }}" class="btn-icon" title="Detail">👁️</a>
                                <form action="{{ route('riwayat.destroy', $t) }}" method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($kodeOrder) }}" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">{{ $transaksi->total() }} transaksi</div>
            <div class="pagination-btns">
                {{ $transaksi->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilter() {
        const q = document.getElementById('searchSetor').value;
        const jenis = document.getElementById('filterJenis').value;
        const status = document.getElementById('filterStatus').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (jenis) params.set('jenis', jenis);
        if (status) params.set('status', status);
        window.location.href = '{{ route('riwayat.index') }}?' + params.toString();
    }
</script>
@endpush