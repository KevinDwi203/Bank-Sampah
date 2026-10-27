@extends('layouts.app')

@section('title', 'Edit Laporan')
@section('pageTitle', 'Edit Laporan')

@section('content')
    <div class="card" style="max-width: 960px;">
        <div class="card-header">
            <div class="card-title"><span>✏️</span> Edit Laporan: {{ $laporan->kode }}</div>
        </div>

        <form action="{{ route('laporan.update', $laporan) }}" method="POST" id="formLaporan">
            @csrf
            @method('PUT')

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">No. Laporan</label>
                    <input type="text" class="form-control" value="{{ $laporan->kode }}" readonly style="background:#f5f5f5; font-weight:600;">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Laporan <span style="color:red">*</span></label>
                    <input type="date" class="form-control" name="tanggal" required value="{{ old('tanggal', $laporan->tanggal->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan / Uraian Laporan <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="keterangan" required value="{{ old('keterangan', $laporan->keterangan) }}">
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label class="form-label" style="font-size: 14px; font-weight: 600; color: #1f2937;">
                    Jenis Sampah &amp; Berat (kg)
                </label>
                <p style="font-size: 12.5px; color: var(--text-muted); margin: 0 0 12px 0;">
                    Ubah berat atau tambahkan jenis sampah sesuai kebutuhan.
                </p>

                <div class="setor-jenis-grid" id="jenisInputs">
                    @foreach ($jenisSampah as $jenis)
                        @php
                            $existingItem = $existingItems->get($jenis->id);
                            $currentBerat = $existingItem ? $existingItem->berat_kg : 0;
                            $currentHarga = $existingItem ? $existingItem->harga_kg : $jenis->harga_asli_kg;
                        @endphp
                        <div class="setor-jenis-item" style="display:flex; flex-direction:column; justify-content:space-between;">
                            <div>
                                <span class="setor-jenis-code">{{ $jenis->kode ?? strtoupper(substr($jenis->nama, 0, 2)) }}</span>
                                <span class="setor-jenis-name">{{ $jenis->ikon ?? '♻️' }} {{ $jenis->nama }}</span>
                                <span class="setor-jenis-price">Harga: {{ formatRupiah($currentHarga) }}/kg</span>
                            </div>

                            <input type="hidden" name="items[{{ $jenis->id }}][harga_kg]" value="{{ $currentHarga }}">
                            
                            <div style="margin-top: 10px;">
                                <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 2px;">Berat (kg):</label>
                                <input type="number" 
                                       class="form-control item-berat-input" 
                                       name="items[{{ $jenis->id }}][berat_kg]" 
                                       min="0" 
                                       step="0.01" 
                                       placeholder="0 kg" 
                                       data-harga="{{ $currentHarga }}"
                                       value="{{ old('items.' . $jenis->id . '.berat_kg', $currentBerat > 0 ? $currentBerat : '') }}"
                                       oninput="calculateTotal()">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group setor-lainnya" style="margin-top: 20px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <label class="form-label" style="font-weight: 600; color: #334155;">Jenis Sampah Lainnya (Opsional)</label>
                <div class="grid-3">
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted);">Nama Jenis</label>
                        <input type="text" class="form-control" name="lainnya[jenis]" id="lainnyaJenis" placeholder="Nama jenis sampah" value="{{ old('lainnya.jenis', $lainnyaItem?->nama_jenis) }}">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted);">Berat (kg)</label>
                        <input type="number" class="form-control" name="lainnya[berat_kg]" id="lainnyaBerat" min="0" step="0.01" placeholder="0 kg" value="{{ old('lainnya.berat_kg', $lainnyaItem?->berat_kg) }}" oninput="calculateTotal()">
                    </div>
                    <div>
                        <label style="font-size: 11px; color: var(--text-muted);">Harga / kg (Rp)</label>
                        <input type="number" class="form-control" name="lainnya[harga_kg]" id="lainnyaHarga" min="0" step="100" placeholder="Rp / kg" value="{{ old('lainnya.harga_kg', $lainnyaItem?->harga_kg) }}" oninput="calculateTotal()">
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">Catatan Tambahan (opsional)</label>
                <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan tambahan...">{{ old('catatan', $laporan->catatan) }}</textarea>
            </div>

            <!-- Live Summary Box -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <span style="font-size: 13px; color: #166534; font-weight: 600;">Total Laporan Ini:</span>
                    <div style="font-size: 12px; color: #15803d; margin-top: 2px;" id="itemCountLabel">0 jenis sampah dipilih</div>
                </div>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="text-align: right;">
                        <span style="font-size: 11.5px; color: #15803d; display: block;">Total Berat</span>
                        <strong style="font-size: 18px; color: #166534;" id="totalBeratLabel">0,00 kg</strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 11.5px; color: #15803d; display: block;">Total Nilai</span>
                        <strong style="font-size: 20px; color: var(--primary);" id="totalNilaiLabel">Rp 0</strong>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('laporan.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function calculateTotal() {
        let totalBerat = 0;
        let totalNilai = 0;
        let itemCount = 0;

        document.querySelectorAll('.item-berat-input').forEach(function(input) {
            const berat = parseFloat(input.value) || 0;
            const harga = parseFloat(input.dataset.harga) || 0;
            if (berat > 0) {
                totalBerat += berat;
                totalNilai += (berat * harga);
                itemCount++;
            }
        });

        const lainnyaBerat = parseFloat(document.getElementById('lainnyaBerat').value) || 0;
        const lainnyaHarga = parseFloat(document.getElementById('lainnyaHarga').value) || 0;
        if (lainnyaBerat > 0) {
            totalBerat += lainnyaBerat;
            totalNilai += (lainnyaBerat * lainnyaHarga);
            itemCount++;
        }

        document.getElementById('totalBeratLabel').textContent = totalBerat.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' kg';
        document.getElementById('totalNilaiLabel').textContent = 'Rp ' + Math.round(totalNilai).toLocaleString('id-ID');
        document.getElementById('itemCountLabel').textContent = itemCount + ' jenis sampah dimasukkan';
    }

    document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endpush
