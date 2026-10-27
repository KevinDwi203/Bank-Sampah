@extends('layouts.app')

@section('title', 'Detail Laporan')
@section('pageTitle', 'Detail Laporan')

@section('content')
    <div class="card" style="max-width: 800px; margin-bottom: 24px;">
        <div class="card-header">
            <div class="card-title"><span>📋</span> Detail Laporan: {{ $laporan->kode }}</div>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('laporan.edit', $laporan) }}" class="btn btn-outline">✏️ Edit</a>
                <a href="{{ route('laporan.index') }}" class="btn btn-primary">← Kembali ke Daftar</a>
            </div>
        </div>

        <div style="padding: 24px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px 20px; background:#f9fafb; padding:18px; border-radius:10px; font-size:13.5px; border:1px solid #e5e7eb;">
                <div><span style="color:var(--text-muted); font-size:11px;">KODE LAPORAN</span><br><strong>{{ $laporan->kode }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TANGGAL</span><br><strong>{{ formatDate($laporan->tanggal) }}</strong></div>
                <div style="grid-column: span 2;"><span style="color:var(--text-muted); font-size:11px;">KETERANGAN</span><br><strong>{{ $laporan->keterangan }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TOTAL BERAT</span><br><strong style="color:#15803d; font-size:16px;">{{ number_format($laporan->total_berat_kg, 2, ',', '.') }} kg</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">TOTAL NILAI</span><br><strong style="color:var(--primary); font-size:16px;">{{ formatRupiah($laporan->total_nilai) }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">PETUGAS PENCATAT</span><br><strong>{{ $laporan->user->nama ?? '-' }}</strong></div>
                <div><span style="color:var(--text-muted); font-size:11px;">WAKTU DIBUAT</span><br><strong>{{ $laporan->created_at->format('d/m/Y H:i') }}</strong></div>
                @if ($laporan->catatan)
                    <div style="grid-column: span 2;"><span style="color:var(--text-muted); font-size:11px;">CATATAN</span><br>{{ $laporan->catatan }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Item Sampah -->
    <div class="card" style="max-width: 800px;">
        <div class="card-header">
            <div class="card-title"><span>📦</span> Rincian Jenis Sampah dalam Laporan Ini</div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Jenis Sampah</th>
                        <th style="text-align: right;">Berat (Kg)</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laporan->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->nama_jenis }}</strong>
                                @if ($item->jenisSampah)
                                    <span style="color:var(--text-muted); font-size:12px;">({{ $item->jenisSampah->kode }})</span>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ number_format($item->berat_kg, 2, ',', '.') }} kg</td>
                            <td style="text-align: right;">{{ formatRupiah($item->harga_kg) }}</td>
                            <td style="text-align: right;"><strong style="color:var(--primary);">{{ formatRupiah($item->subtotal) }}</strong></td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #f8fafc;">
                        <td colspan="2">TOTAL</td>
                        <td style="text-align: right; color:#15803d;">{{ number_format($laporan->total_berat_kg, 2, ',', '.') }} kg</td>
                        <td style="text-align: right;">—</td>
                        <td style="text-align: right; color:var(--primary);">{{ formatRupiah($laporan->total_nilai) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
