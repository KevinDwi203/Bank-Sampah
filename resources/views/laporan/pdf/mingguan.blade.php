<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mingguan - {{ Setting::get('nama_utama', 'Bank Sampah') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2e7d32; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 22px; color: #2e7d32; }
        .header h2 { margin: 5px 0 0; font-size: 16px; font-weight: normal; color: #666; }
        .header p { margin: 5px 0 0; font-size: 11px; color: #888; }
        .period-info { background: #f1f8e9; padding: 10px; border-radius: 6px; margin-bottom: 20px; font-size: 12px; }
        .period-info strong { color: #2e7d32; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background: #2e7d32; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #e8f5e9 !important; }
        .nasabah-header { background: #e8f5e9; font-weight: bold; }
        .summary { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 6px; }
        .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
        .summary-row.total { font-weight: bold; font-size: 13px; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 30%; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
        @page { margin: 15mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ Setting::get('nama_utama', 'Bank Sampah') }}</h1>
        <h2>{{ Setting::get('subjudul', 'Hijau Lestari') }}</h2>
        <p>{{ Setting::get('alamat', '') }} | {{ Setting::get('telepon', '') }}</p>
    </div>

    <div class="period-info">
        <strong>Laporan Mingguan Setoran Sampah</strong><br>
        Periode: {{ formatDate($dari) }} s.d. {{ formatDate($sampai) }} | Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}
    </div>

    @foreach ($data as $rekap)
        <table>
            <thead>
                <tr class="nasabah-header">
                    <td colspan="5">{{ formatDate($rekap['tanggal']) }} - {{ $rekap['nasabah']->nama }} ({{ $rekap['nasabah']->kode }})</td>
                </tr>
                <tr>
                    <th style="width:30%">Jenis Sampah</th>
                    <th class="text-center" style="width:15%">Berat (kg)</th>
                    <th class="text-right" style="width:20%">Harga/kg</th>
                    <th class="text-right" style="width:20%">Total</th>
                    <th class="text-center" style="width:15%">Poin</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rekap['items'] as $item)
                    <tr>
                        <td>{{ $item->jenisSampah->ikon ?? '♻️' }} {{ $item->jenisSampah->nama }}</td>
                        <td class="text-center">{{ number_format($item->berat_kg, 2) }}</td>
                        <td class="text-right">{{ formatRupiah($item->harga_warga_kg) }}</td>
                        <td class="text-right">{{ formatRupiah($item->total) }}</td>
                        <td class="text-center">{{ $item->poin_dapat }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-center">{{ number_format($rekap['total_kg'], 2) }}</td>
                    <td></td>
                    <td class="text-right">{{ formatRupiah($rekap['total_nilai']) }}</td>
                    <td class="text-center">{{ collect($rekap['items'])->sum('poin_dapat') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="summary">
        <div class="summary-row">
            <span>Total Setoran</span>
            <span>{{ count($data) }} nasabah</span>
        </div>
        <div class="summary-row">
            <span>Total Berat Sampah</span>
            <span>{{ number_format(collect($data)->sum('total_kg'), 2) }} kg</span>
        </div>
        <div class="summary-row">
            <span>Total Transaksi</span>
            <span>{{ collect($data)->sum(fn($d) => count($d['items'])) }} item</span>
        </div>
        <div class="summary-row total">
            <span>Total Nilai Setoran</span>
            <span>{{ formatRupiah(collect($data)->sum('total_nilai')) }}</span>
        </div>
    </div>

    <div class="footer">
        <div class="signature">
            <div class="signature-line">Petugas</div>
        </div>
        <div class="signature">
            <div class="signature-line">Bendahara</div>
        </div>
        <div class="signature">
            <div class="signature-line">Kepala Bank Sampah</div>
        </div>
    </div>
</body>
</html>