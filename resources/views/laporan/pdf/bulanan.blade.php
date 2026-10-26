<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan - {{ Setting::get('nama_utama', 'Bank Sampah') }}</title>
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
        .section-title { font-weight: bold; font-size: 11px; margin: 15px 0 5px; color: #2e7d32; border-bottom: 1px solid #2e7d32; padding-bottom: 3px; }
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
        <strong>Laporan Bulanan</strong> Bulan {{ $bulan }} Tahun {{ $tahun }} | Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}
    </div>

    <!-- Ringkasan -->
    <div class="summary">
        <div class="summary-row">
            <span>Total Sampah</span>
            <span>{{ number_format($data['ringkasan']['total_kg'], 1) }} kg</span>
        </div>
        <div class="summary-row">
            <span>Total Nilai</span>
            <span>{{ formatRupiah($data['ringkasan']['total_nilai']) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Transaksi</span>
            <span>{{ $data['ringkasan']['total_transaksi'] }}</span>
        </div>
        <div class="summary-row">
            <span>Nasabah Aktif</span>
            <span>{{ $data['ringkasan']['nasabah_aktif'] }}</span>
        </div>
    </div>

    <!-- Per Nasabah per Minggu -->
    <div class="section-title">Rekap per Nasabah per Minggu</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kartu</th>
                <th>Nama</th>
                @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                    <th class="text-center">M{{ $i + 1 }}</th>
                @endforeach
                <th class="text-right">Total Kg</th>
                <th class="text-right">Total Rp</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['per_nasabah']['data'] as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['nasabah']->kode }}</td>
                    <td>{{ $item['nasabah']->nama }}</td>
                    @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                        <td class="text-right">{{ number_format($item['minggu'][$i + 1]['kg'] ?? 0, 1) }}</td>
                    @endforeach
                    <td class="text-right"><strong>{{ number_format($item['total_kg'], 1) }}</strong></td>
                    <td class="text-right"><strong>{{ formatRupiah($item['total_nilai']) }}</strong></td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                @foreach ($data['per_nasabah']['weeks'] as $i => $week)
                    <td class="text-right">{{ number_format(collect($data['per_nasabah']['data'])->sum('minggu.' . ($i + 1) . '.kg'), 1) }}</td>
                @endforeach
                <td class="text-right">{{ number_format(collect($data['per_nasabah']['data'])->sum('total_kg'), 1) }}</td>
                <td class="text-right">{{ formatRupiah(collect($data['per_nasabah']['data'])->sum('total_nilai')) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Per Jenis Sampah -->
    <div class="section-title">Rekap per Jenis Sampah</div>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="text-center">Kategori</th>
                <th class="text-right">Total Kg</th>
                <th class="text-right">Total Rp</th>
                <th class="text-center">Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['per_jenis'] as $item)
                <tr>
                    <td>{{ $item['ikon'] ?? '♻️' }} {{ $item['nama'] }}</td>
                    <td class="text-center">{{ $item['kategori'] }}</td>
                    <td class="text-right">{{ number_format($item['total_kg'], 1) }}</td>
                    <td class="text-right">{{ formatRupiah($item['total_nilai']) }}</td>
                    <td class="text-center">{{ $item['jumlah_transaksi'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

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