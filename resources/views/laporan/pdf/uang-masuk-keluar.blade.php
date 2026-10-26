<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Uang Masuk/Keluar - {{ Setting::get('nama_utama', 'Bank Sampah') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #2e7d32; }
        .header h2 { margin: 3px 0 0; font-size: 14px; font-weight: normal; color: #666; }
        .period-info { background: #f1f8e9; padding: 8px; border-radius: 4px; margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
        th, td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        th { background: #2e7d32; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background: #e8f5e9 !important; }
        .jenis-seton { color: #2e7d32; }
        .jenis-tarik { color: #e65100; }
        .jenis-oper { color: #1565c0; }
        .summary { margin-top: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px; font-size: 11px; }
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
    </div>

    <div class="period-info">
        <strong>Laporan Uang Masuk / Keluar</strong> Bulan {{ $bulan }} Tahun {{ $tahun }} | Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:12%">Tanggal</th>
                <th style="width:30%">Keterangan</th>
                <th style="width:10%">Jenis</th>
                <th class="text-right" style="width:15%">Masuk</th>
                <th class="text-right" style="width:15%">Keluar</th>
                <th class="text-right" style="width:15%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ formatDate($item['tanggal']) }}</td>
                    <td>{{ $item['keterangan'] }}</td>
                    <td class="text-center">
                        <span class="badge-status {{ 
                            $item['jenis'] === 'setoran' ? 'success' : 
                            ($item['jenis'] === 'penarikan' ? 'warning' : 'info') 
                        }}" style="font-size: 8px; padding: 2px 6px;">
                            {{ ucfirst($item['jenis']) }}
                        </span>
                    </td>
                    <td class="text-right">{{ $item['masuk'] > 0 ? formatRupiah($item['masuk']) : '-' }}</td>
                    <td class="text-right">{{ $item['keluar'] > 0 ? formatRupiah($item['keluar']) : '-' }}</td>
                    <td class="text-right"><strong>{{ formatRupiah($item['saldo']) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $totalMasuk = collect($data)->sum('masuk');
        $totalKeluar = collect($data)->sum('keluar');
        $saldoAkhir = collect($data)->last()['saldo'] ?? 0;
    @endphp

    <div class="summary">
        <div class="summary-row">
            <span>Total Uang Masuk</span>
            <span>{{ formatRupiah($totalMasuk) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Uang Keluar</span>
            <span>{{ formatRupiah($totalKeluar) }}</span>
        </div>
        <div class="summary-row total">
            <span>Saldo Akhir</span>
            <span>{{ formatRupiah($saldoAkhir) }}</span>
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