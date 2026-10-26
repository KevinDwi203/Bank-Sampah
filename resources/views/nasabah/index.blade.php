@extends('layouts.app')

@section('title', 'Data Nasabah')
@section('pageTitle', 'Data Nasabah')

@section('content')
    <!-- STATS -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="stat-card green">
            <div class="stat-icon green">👥</div>
            <div class="stat-info"><h3>{{ $stats['total'] }}</h3><p>Total Nasabah</p></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">✅</div>
            <div class="stat-info"><h3>{{ $stats['aktif'] }}</h3><p>Nasabah Aktif</p></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue">🆕</div>
            <div class="stat-info"><h3>{{ $stats['baru_bulan_ini'] }}</h3><p>Nasabah Baru Bulan Ini</p></div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red">💤</div>
            <div class="stat-info"><h3>{{ $stats['tidak_aktif'] }}</h3><p>Tidak Aktif</p></div>
        </div>
    </div>

    <!-- TABEL NASABAH -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>👥</span> Daftar Nasabah</div>
            <div style="display:flex; gap:10px;">
                <select class="form-control" style="width:auto; padding:8px 12px; font-size:13px;" id="filterStatusNasabah" onchange="applyFilterNasabah()">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak Aktif" {{ request('status') === 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchNasabah" placeholder="Cari nama, No. Kartu, atau nomor telepon..." value="{{ request('q') }}" oninput="applyFilterNasabah()">
            </div>
            <a href="{{ route('nasabah.create') }}" class="btn btn-primary">＋ Tambah</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. Kartu</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th>Saldo Tabungan</th>
                        <th>Status</th>
                        <th>Setoran Minggu Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="nasabahTable">
                    @forelse ($nasabah as $n)
                        <tr>
                            <td><strong>{{ $n->kode }}</strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:36px; height:36px; background:#e8f5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px;">👤</div>
                                    <strong>{{ $n->nama }}</strong>
                                </div>
                            </td>
                            <td>{{ $n->telepon ?? '—' }}</td>
                            <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $n->alamat ?? '—' }}</td>
                            <td><strong style="color:var(--primary);">{{ formatRupiah($n->saldo) }}</strong></td>
                            <td><span class="badge-status {{ $n->status === 'Aktif' ? 'success' : 'danger' }}">● {{ $n->status }}</span></td>
                            <td>
                                <strong style="color:var(--primary);">{{ $setoranMingguIni[$n->id] ?? 0 }} kali</strong>
                            </td>
                            <td>
                                <a href="{{ route('nasabah.show', $n) }}" class="btn-icon" title="Detail">👁️</a>
                                <a href="{{ route('nasabah.edit', $n) }}" class="btn-icon" title="Edit" style="margin-left:8px;">✏️</a>
                                <form action="{{ route('nasabah.destroy', $n) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus nasabah \"{{ $n->nama }}\"? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus" style="margin-left:8px; background:none; border:none; cursor:pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada nasabah ditemukan</td>
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
    function applyFilterNasabah() {
        const q = document.getElementById('searchNasabah').value;
        const status = document.getElementById('filterStatusNasabah').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (status) params.set('status', status);
        window.location.href = '{{ route('nasabah.index') }}?' + params.toString();
    }
</script>
@endpush