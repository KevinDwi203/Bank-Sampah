@extends('layouts.app')

@section('title', 'Tabungan')
@section('pageTitle', 'Tabungan')

@section('content')
    <!-- STATS -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">💰</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['total_saldo']) }}</h3><p>Total Saldo Semua Nasabah</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">📥</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['uang_masuk_bulan_ini']) }}</h3><p>Uang Masuk Bulan Ini</p></div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red">📤</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['uang_ditarik_bulan_ini']) }}</h3><p>Uang Ditarik Bulan Ini</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">👥</div>
            <div class="stat-info"><h3>{{ $stats['nasabah_aktif'] }}</h3><p>Nasabah Aktif</p></div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red">💤</div>
            <div class="stat-info"><h3>{{ $stats['nasabah_tidak_aktif'] }}</h3><p>Nasabah Tidak Aktif</p></div>
        </div>
    </div>

    <!-- DAFTAR TABUNGAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>💰</span> Saldo Tabungan Nasabah</div>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchTabungan" placeholder="Cari nama atau No. Kartu..." oninput="applyFilterTabungan()">
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. Kartu</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Saldo</th>
                        <th>Total Setoran</th>
                        <th>Total Penarikan</th>
                        <th>Setoran Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nasabah as $n)
                        <tr>
                            <td><strong>{{ $n->kode }}</strong></td>
                            <td>{{ $n->nama }}</td>
                            <td><span class="badge-status {{ $n->status === 'Aktif' ? 'success' : 'danger' }}">● {{ $n->status }}</span></td>
                            <td><strong style="color:var(--primary);">{{ formatRupiah($n->saldo) }}</strong></td>
                            <td>{{ formatRupiah($n->total_setoran ?? 0) }}</td>
                            <td>{{ formatRupiah($n->total_penarikan ?? 0) }}</td>
                            <td>{{ $n->last_deposit_at ? formatDate($n->last_deposit_at) : '—' }}</td>
                            <td>
                                <a href="{{ route('tabungan.show', $n) }}" class="btn-icon" title="Detail">👁️</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">{{ $nasabah->total() }} nasabah</div>
            <div class="pagination-btns">
                {{ $nasabah->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilterTabungan() {
        const q = document.getElementById('searchTabungan').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        window.location.href = '{{ route('tabungan.index') }}?' + params.toString();
    }
</script>
@endpush