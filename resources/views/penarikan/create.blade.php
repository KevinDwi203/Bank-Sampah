@extends('layouts.app')

@section('title', 'Tambah Transaksi')
@section('pageTitle', 'Tambah Transaksi')

@section('content')
    <div class="card" style="max-width: 600px;">
        <div class="card-header">
            <div class="card-title"><span>🏧</span> Tambah Transaksi</div>
        </div>

        <form action="{{ route('penarikan.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nasabah</label>
                <select class="form-control" name="nasabah_id" required>
                    <option value="">— Pilih Nasabah —</option>
                    @foreach ($nasabah as $n)
                        <option value="{{ $n->id }}" data-saldo="{{ $n->saldo }}">
                            {{ $n->kode }} — {{ $n->nama }} (Saldo: {{ formatRupiah($n->saldo) }}) [{{ $n->status }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="infoSaldo" style="display:none; background:#f1f8e9; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; border-left:3px solid var(--primary);">
                Saldo tersedia: <strong id="saldoTersedia">—</strong>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal <span style="color:red">*</span></label>
                <input type="date" class="form-control" name="tanggal" required value="{{ now()->format('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Jumlah (Rp) <span style="color:red">*</span></label>
                <div class="input-group">
                    <span class="input-icon">💵</span>
                    <input type="number" class="form-control" name="jumlah" min="1000" step="1000" required placeholder="Masukkan jumlah penarikan">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Metode</label>
                <select class="form-control" name="metode">
                    <option value="Tunai">Tunai</option>
                    <option value="Transfer Bank">Transfer Bank</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea class="form-control" name="catatan" rows="2" placeholder="Tambahkan catatan..."></textarea>
            </div>

            <div class="modal-footer" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('penarikan.index') }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Proses Transaksi</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelector('select[name="nasabah_id"]').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const infoBox = document.getElementById('infoSaldo');
        
        if (!option.value) {
            infoBox.style.display = 'none';
            return;
        }
        
        infoBox.style.display = 'block';
        document.getElementById('saldoTersedia').textContent = formatRupiah(option.dataset.saldo);
    });
</script>
@endpush