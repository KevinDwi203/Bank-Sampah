<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kiloan - {{ Setting::get('nama_utama', 'Bank Sampah') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; margin: 0; padding: 15px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #2e7d32; }
        .header h2 { margin: 3px 0 0; font-size: 13px; font-weight: normal; color: #666; }
        .period-info { background: #f1f8e9; padding: 8px; border-radius: 4px; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px; }
        th, td { border: 1px solid #ddd; padding: 3px 5px; text-align: left; }
        th { background: #2e7d32; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #e8f5e9 !important; }
        .summary { margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px; font-size: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin: 3px 0; }
        .summary-row.total { font-weight: bold; font-size: 11px; border-top: 1px solid #ddd; padding-top: 5px; margin-top: 5px; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 30%; }
        .signature-line { border-top: 1px solid #333; margin-top: 30px; padding-top: 3px; font-size: 9px; }
        @page { margin: 10mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ Setting::get('nama_utama', 'Bank Sampah') }}</h1>
        <h2>{{ Setting::get('subjudul', 'Hijau Lestari') }}</h2>
    </div>

    <div class="period-info">
        <strong>Laporan Setoran Kiloan</strong> Periode: {{ formatDate($dari) }} s.d. {{ formatDate($sampai) }} | Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Tgl</th>
                <th>Kode</th>
                <th>Jenis Barang</th>
                <th class="text-right">Harga Warga/kg</th>
                <th class="text-right">Harga Asli/kg</th>
                <th class="text-center">Berat (kg)</th>
                <th class="text-right">Jumlah Harga (Warga)</th>
                <th class="text-right">Jumlah Harga (Asli)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ formatShortDate($item['tanggal']) }}</td>
                    <td>{{ $item['kode'] ?? '-' }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="text-right">{{ formatRupiah($item['harga_kg']) }}</td>
                    <td class="text-right">{{ formatRupiah($item['harga_asli_kg']) }}</td>
                    <td class="text-center">{{ number_format($item['berat_kg'], 2) }}</td>
                    <td class="text-right">{{ formatRupiah($item['jumlah_harga']) }}</td>
                    <td class="text-right">{{ formatRupiah($item['harga_asli_kg'] * $item['berat_kg']) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5">TOTAL</td>
                <td class="text-center">{{ number_format(collect($data)->sum('berat_kg'), 2) }}</td>
                <td class="text-right">{{ formatRupiah(collect($data)->sum('jumlah_harga')) }}</td>
                <td class="text-right">{{ formatRupiah(collect($data)->sum(fn($d) => $d['harga_asli_kg'] * $d['berat_kg'])) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Total Berat</span>
            <span>{{ number_format(collect($data)->sum('berat_kg'), 2) }} kg</span>
        </div>
        <div class="summary-row">
            <span>Total Nilai (Harga ke Warga)</span>
            <span>{{ formatRupiah(collect($data)->sum('jumlah_harga')) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Nilai (Harga Asli)</span>
            <span>{{ formatRupiah(collect($data)->sum(fn($d) => $d['harga_asli_kg'] * $d['berat_kg'])) }}</span>
        </div>
        <div class="summary-row total">
            <span>Selisih</span>
            <span>{{ formatRupiah(collect($data)->sum(fn($d) => ($d['harga_asli_kg'] - $d['harga_kg']) * $d['berat_kg'])) }}</span>
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