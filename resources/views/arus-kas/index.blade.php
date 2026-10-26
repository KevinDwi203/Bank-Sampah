@extends('layouts.app')

@section('title', 'Arus Kas')
@section('pageTitle', 'Arus Kas')

@section('content')
    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">📥</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['total_masuk']) }}</h3><p>Total Masuk</p></div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red">📤</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['total_keluar']) }}</h3><p>Total Keluar</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">💰</div>
            <div class="stat-info"><h3>{{ formatRupiah($stats['saldo_akhir']) }}</h3><p>Saldo Akhir</p></div>
        </div>
    </div>

    <!-- Tabel Arus Kas -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>💸</span> Daftar Arus Kas</div>
            <a href="{{ route('arus-kas.create') }}" class="btn btn-primary">＋ Tambah</a>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchArusKas" placeholder="Cari keterangan..." oninput="applyFilterArusKas()">
            </div>
            <select class="form-control" style="width:auto; padding:8px 12px; font-size:13px;" id="filterKategori" onchange="applyFilterArusKas()">
                <option value="">Semua Kategori</option>
                <option value="pengeluaran_operasional" {{ request('kategori') === 'pengeluaran_operasional' ? 'selected' : '' }}>Pengeluaran Operasional</option>
                <option value="pembayaran_pengurus" {{ request('kategori') === 'pembayaran_pengurus' ? 'selected' : '' }}>Pembayaran Pengurus</option>
                <option value="pembelian_peralatan" {{ request('kategori') === 'pembelian_peralatan' ? 'selected' : '' }}>Pembelian Peralatan</option>
                <option value="biaya_transport" {{ request('kategori') === 'biaya_transport' ? 'selected' : '' }}>Biaya Transport</option>
                <option value="biaya_konsumsi" {{ request('kategori') === 'biaya_konsumsi' ? 'selected' : '' }}>Biaya Konsumsi</option>
                <option value="lainnya" {{ request('kategori') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Kategori</th>
                        <th class="text-right">Masuk</th>
                        <th class="text-right">Keluar</th>
                        <th class="text-right">Saldo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($arusKas as $ak)
                        <tr>
                            <td>{{ formatDate($ak->tanggal) }}</td>
                            <td>{{ $ak->keterangan }}</td>
                            <td><span class="badge-status info">{{ $ak->kategori_label }}</span></td>
                            <td class="text-right">{{ $ak->masuk > 0 ? formatRupiah($ak->masuk) : '-' }}</td>
                            <td class="text-right">{{ $ak->keluar > 0 ? formatRupiah($ak->keluar) : '-' }}</td>
                            <td class="text-right"><strong>{{ formatRupiah($ak->saldo) }}</strong></td>
                            <td>
                                @can('Administrator')
                                <a href="{{ route('arus-kas.edit', $ak) }}" class="btn-icon" title="Edit">✏️</a>
                                <form action="{{ route('arus-kas.destroy', $ak) }}" method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm('Hapus arus kas ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data arus kas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">{{ $arusKas->total() }} item</div>
            <div class="pagination-btns">
                {{ $arusKas->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilterArusKas() {
        const q = document.getElementById('searchArusKas').value;
        const kategori = document.getElementById('filterKategori').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (kategori) params.set('kategori', kategori);
        window.location.href = '{{ route('arus-kas.index') }}?' + params.toString();
    }
</script>
@endpush