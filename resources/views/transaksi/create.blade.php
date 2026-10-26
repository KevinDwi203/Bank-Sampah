@extends('layouts.app')

@section('title', 'Tambah Setoran')
@section('pageTitle', 'Tambah Setoran')

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <div class="card-title"><span>📦</span> Tambah Setoran Sampah</div>
        </div>

        <form action="{{ route('riwayat.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nasabah</label>
                <select class="form-control" name="nasabah_id" required onchange="updateNasabahInfo()">
                    <option value="">— Pilih Nasabah —</option>
                    @foreach ($nasabah as $n)
                        <option value="{{ $n->id }}" data-saldo="{{ $n->saldo }}" data-poin="{{ $n->poin }}">
                            {{ $n->kode }} — {{ $n->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="infoNasabah" style="display:none; background:#f1f8e9; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; border-left:3px solid var(--primary);">
                <strong id="infNama">—</strong> &nbsp;|&nbsp;
                <span>Saldo: <strong id="infSaldo">—</strong></span> &nbsp;|&nbsp;
                <span>Poin: <strong id="infPoin">—</strong></span>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal <span style="color:red">*</span></label>
                <input type="date" class="form-control" name="tanggal" required value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Berat Jenis Sampah (kg)</label>
                <div class="setor-jenis-grid" id="jenisInputs">
                    @foreach ($jenisSampah as $jenis)
                        <label class="setor-jenis-item">
                            <span class="setor-jenis-code">{{ $jenis->kode ?? strtoupper(substr($jenis->nama, 0, 2)) }}</span>
                            <span class="setor-jenis-name">{{ $jenis->ikon ?? '♻️' }} {{ $jenis->nama }}</span>
                            <span class="setor-jenis-price">{{ formatRupiah($jenis->harga_warga_kg) }}/kg</span>
                            <input type="hidden" name="items[{{ $jenis->id }}][jenis_id]" value="{{ $jenis->id }}">
                            <input type="number" class="form-control setor-jenis-input" name="items[{{ $jenis->id }}][berat_kg]" min="0" step="0.01" placeholder="0" data-jenis-id="{{ $jenis->id }}">
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group setor-lainnya">
                <label class="form-label">Lainnya</label>
                <div class="grid-3">
                    <input type="text" class="form-control" name="lainnya[jenis]" id="lainnyaJenis" placeholder="Jenis sampah">
                    <input type="number" class="form-control" name="lainnya[berat_kg]" id="lainnyaBerat" min="0" step="0.01" placeholder="Berat (kg)">
                    <input type="number" class="form-control" name="lainnya[harga_kg]" id="lainnyaTotal" min="0" step="100" placeholder="Harga/kg (Rp)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea class="form-control" name="catatan" rows="2" placeholder="Tambahkan catatan..."></textarea>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('riwayat.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Submit Setoran</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function updateNasabahInfo() {
        const select = document.querySelector('select[name="nasabah_id"]');
        const option = select.options[select.selectedIndex];
        const infoBox = document.getElementById('infoNasabah');
        
        if (!option.value) {
            infoBox.style.display = 'none';
            return;
        }
        
        infoBox.style.display = 'block';
        document.getElementById('infNama').textContent = option.text;
        document.getElementById('infSaldo').textContent = formatRupiah(option.dataset.saldo);
        document.getElementById('infPoin').textContent = option.dataset.poin + ' poin';
    }
</script>
@endpush