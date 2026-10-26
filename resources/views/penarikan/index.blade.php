@extends('layouts.app')

@section('title', 'Transaksi')
@section('pageTitle', 'Transaksi')

@section('content')
    <!-- STATS -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">🏧</div>
            <div class="stat-info"><h3>{{ $stats['bulan_ini'] }}</h3><p>Transaksi Bulan Ini</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">💵</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['total_nominal']) }}</h3><p>Total Nominal</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">⏳</div>
            <div class="stat-info"><h3>{{ $stats['pending'] }}</h3><p>Pending</p></div>
        </div>
    </div>

    <!-- TABEL PENARIKAN -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>🏧</span> Daftar Transaksi</div>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchPenarikan" placeholder="Cari nama nasabah atau kode..." value="{{ request('q') }}" oninput="applyFilterPenarikan()">
            </div>
            <select class="form-control" style="width:auto; padding:8px 12px; font-size:13px;" id="filterStatus" onchange="applyFilterPenarikan()">
                <option value="">Semua Status</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Dibatalkan" {{ request('status') === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <a href="{{ route('penarikan.create') }}" class="btn btn-primary">＋ Transaksi Baru</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Nasabah</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penarikan as $p)
                        <tr>
                            <td><strong>{{ $p->kode }}</strong></td>
                            <td>{{ formatDate($p->tanggal) }}</td>
                            <td>{{ $p->nasabah->nama }} ({{ $p->nasabah->kode }})</td>
                            <td><strong>{{ formatRupiah($p->jumlah) }}</strong></td>
                            <td>{{ $p->metode }}</td>
                            <td>
                                <span class="badge-status {{ $p->status === 'Selesai' ? 'success' : ($p->status === 'Pending' ? 'warning' : 'danger') }}">● {{ $p->status }}</span>
                            </td>
                            <td>
                                <a href="{{ route('penarikan.show', $p) }}" class="btn-icon" title="Detail">👁️</a>
                                
                                <form action="{{ route('penarikan.updateStatus', $p) }}" method="POST" style="display:inline; margin-left:8px;">
                                    @csrf @method('PUT')
                                    <select name="status" class="form-control" style="width:auto; display:inline-block;" onchange="this.form.submit()">
                                        <option value="Selesai" {{ $p->status === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="Pending" {{ $p->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Dibatalkan" {{ $p->status === 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                </form>
                                
                                @if ($p->status !== 'Selesai')
                                    <form action="{{ route('penarikan.destroy', $p) }}" method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon" title="Hapus" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data transaksi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">{{ $penarikan->total() }} transaksi</div>
            <div class="pagination-btns">
                {{ $penarikan->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilterPenarikan() {
        const q = document.getElementById('searchPenarikan').value;
        const status = document.getElementById('filterStatus').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (status) params.set('status', status);
        window.location.href = '{{ route('penarikan.index') }}?' + params.toString();
    }
</script>
@endpush