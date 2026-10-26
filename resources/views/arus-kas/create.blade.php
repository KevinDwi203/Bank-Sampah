@extends('layouts.app')

@section('title', 'Tambah Arus Kas')
@section('pageTitle', 'Tambah Arus Kas')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>💸</span> Tambah Arus Kas</div>
        </div>

        <form action="{{ route('arus-kas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Tanggal <span style="color:red">*</span></label>
                <input type="date" class="form-control" name="tanggal" required value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="keterangan" required placeholder="Contoh: Beli tali rafia" maxlength="255">
            </div>

            <div class="form-group">
                <label class="form-label">Kategori <span style="color:red">*</span></label>
                <select class="form-control" name="kategori" required>
                    <option value="pengeluaran_operasional">Pengeluaran Operasional</option>
                    <option value="pembayaran_pengurus">Pembayaran Pengurus</option>
                    <option value="pembelian_peralatan">Pembelian Peralatan</option>
                    <option value="biaya_transport">Biaya Transport</option>
                    <option value="biaya_konsumsi">Biaya Konsumsi</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Masuk (Rp)</label>
                    <div class="input-group">
                        <span class="input-icon">📥</span>
                        <input type="number" class="form-control" name="masuk" min="0" step="1000" placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keluar (Rp)</label>
                    <div class="input-group">
                        <span class="input-icon">📤</span>
                        <input type="number" class="form-control" name="keluar" min="0" step="1000" placeholder="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea class="form-control" name="catatan" rows="2" placeholder="Tambahkan catatan..."></textarea>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('arus-kas.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
@endsection