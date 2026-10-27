<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Laporan Sampah - {{ Setting::get('nama_utama', 'Bank Sampah') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #2e7d32; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #2e7d32; }
        .header h2 { margin: 3px 0 0; font-size: 13px; font-weight: normal; color: #666; }
        .title { text-align: center; margin: 0 0 4px; font-size: 15px; font-weight: bold; }
        .period { text-align: center; margin: 0 0 16px; font-size: 11px; color: #555; }
        .section-title { font-size: 12px; font-weight: bold; margin: 16px 0 6px; color: #166534; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background: #2e7d32; color: #fff; font-weight: bold; text-align: left; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #e8f5e9 !important; }
        .printed { font-size: 9px; color: #777; margin-top: 4px; }
        .sign { width: 100%; margin-top: 30px; border: none; }
        .sign td { border: none; text-align: center; width: 33%; padding: 0 10px; }
        .sign .line { border-top: 1px solid #333; margin-top: 45px; padding-top: 3px; font-size: 10px; }
        @page { margin: 12mm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ Setting::get('nama_utama', 'Bank Sampah') }}</h1>
        <h2>{{ Setting::get('subjudul', 'Hijau Lestari') }}</h2>
    </div>

    <p class="title">Laporan Rekapitulasi Sampah</p>
    <p class="period">
        Periode: {{ \Carbon\Carbon::parse($dari)->locale('id')->isoFormat('D MMMM YYYY') }}
        s.d. {{ \Carbon\Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM YYYY') }}
    </p>

    <div class="section-title">1. Rekapitulasi per Jenis Sampah</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:40px;">No.</th>
                <th>Jenis Sampah</th>
                <th class="text-right">Total Berat (Kg)</th>
                <th class="text-right">Rata-rata Harga/Kg</th>
                <th class="text-right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap['items'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="text-right">{{ number_format($item['total_kg'], 2, ',', '.') }} kg</td>
                    <td class="text-right">{{ formatRupiah(round($item['harga_asli_kg'])) }}</td>
                    <td class="text-right">{{ formatRupiah($item['total_nilai']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada laporan pada periode ini</td>
                </tr>
            @endforelse
            @if (count($rekap['items']) > 0)
                <tr class="total-row">
                    <td colspan="2">Total Keseluruhan</td>
                    <td class="text-right">{{ number_format($rekap['total_kg'], 2, ',', '.') }} kg</td>
                    <td class="text-right">-</td>
                    <td class="text-right">{{ formatRupiah($rekap['total_nilai']) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if (isset($laporans) && count($laporans) > 0)
        <div class="section-title">2. Rincian Laporan Manual yang Dicatat</div>
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width:30px;">No.</th>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-right">Total Berat</th>
                    <th class="text-right">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporans as $i => $lap)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td><strong>{{ $lap->kode }}</strong></td>
                        <td>{{ formatDate($lap->tanggal) }}</td>
                        <td>{{ $lap->keterangan }}</td>
                        <td class="text-right">{{ number_format($lap->total_berat_kg, 2, ',', '.') }} kg</td>
                        <td class="text-right">{{ formatRupiah($lap->total_nilai) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="printed">Dicetak: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>

    <table class="sign">
        <tr>
            <td><div class="line">Petugas</div></td>
            <td><div class="line">Bendahara</div></td>
            <td><div class="line">Kepala Bank Sampah</div></td>
        </tr>
    </table>
</body>
</html>
