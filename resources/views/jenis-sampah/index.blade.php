@extends('layouts.app')

@section('title', 'Harga Sampah')
@section('pageTitle', 'Harga Sampah')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title"><span>🏷️</span> Daftar Harga Sampah</div>
            <a href="{{ route('jenis-sampah.create') }}" class="btn btn-primary">＋ Tambah Jenis</a>
        </div>

        <div class="search-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchJenis" placeholder="Cari nama jenis sampah (Enter)..." value="{{ request('q') }}" onkeydown="if(event.key==='Enter'){applyFilterJenis()}">
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Ikon</th>
                        <th>Nama</th>
                        <th>Harga Warga/kg</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jenisSampah as $j)
                        <tr>
                            <td><strong>{{ $j->kode ?? '-' }}</strong></td>
                            <td style="font-size:20px;">{{ $j->ikon ?? '♻️' }}</td>
                            <td>{{ $j->nama }}</td>
                            <td><strong>{{ formatRupiah($j->harga_warga_kg) }}</strong></td>
                            <td>
                                <a href="{{ route('jenis-sampah.edit', $j) }}" class="btn-icon" title="Edit">✏️</a>
                                <form action="{{ route('jenis-sampah.destroy', $j) }}" method="POST" style="display:inline; margin-left:8px;" onsubmit="return confirm(@js($j->transaksi()->exists() ? 'Jenis ini memiliki transaksi. Status akan diubah jadi tidak aktif.' : 'Hapus jenis sampah ini?'))">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon" title="Hapus" style="background:none; border:none; cursor:pointer;">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data jenis sampah</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">{{ $jenisSampah->total() }} jenis</div>
            <div class="pagination-btns">
                {{ $jenisSampah->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function applyFilterJenis() {
        const q = document.getElementById('searchJenis').value;
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        window.location.href = '{{ route('jenis-sampah.index') }}?' + params.toString();
    }
</script>
@endpush