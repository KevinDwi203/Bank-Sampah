@extends('layouts.app')

@section('title', 'Dashboard')
@section('pageTitle', 'Dashboard')

@section('content')
    <!-- STAT CARDS -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon blue">👥</div>
            <div class="stat-info">
                <h3>{{ $stats['total_nasabah_aktif'] }}</h3>
                <p>Total Nasabah Aktif</p>
                <div class="stat-change up">+{{ $stats['nasabah_baru_bulan_ini'] }} nasabah baru bulan ini</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info">
                <h3>{{ number_format($stats['total_sampah_bulan_ini'], 1) }} kg</h3>
                <p>Total Sampah Bulan Ini</p>
                <div class="stat-change up">{{ number_format($stats['total_sampah_semua'], 1) }} kg total</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green">♻️</div>
            <div class="stat-info">
                <h3>{{ number_format($stats['total_sampah_minggu_ini'], 1) }} kg</h3>
                <p>Total Sampah Minggu Ini</p>
                <div class="stat-change up">{{ $stats['transaksi_minggu_ini'] }} transaksi</div>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-icon yellow">📋</div>
            <div class="stat-info">
                <h3>{{ $stats['transaksi_minggu_ini'] }}</h3>
                <p>Transaksi Minggu Ini</p>
                <div class="stat-change up">Rp {{ number_format($stats['total_nilai_bulan_ini'], 0) }} nilai bulan ini</div>
            </div>
        </div>
    </div>

    <!-- CHART + SALDO -->
    <div class="two-col" style="margin-bottom: 24px;">

        <!-- CHART -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><span>📊</span> Grafik Setoran Bulanan</div>
                <select class="form-control" style="width:auto; padding:6px 12px; font-size:12px;" id="chartYear">
                    <option value="{{ now()->year }}">{{ now()->year }}</option>
                    <option value="{{ (int)now()->year - 1 }}">{{ (int)now()->year - 1 }}</option>
                </select>
            </div>
            <div class="chart-container chart-axis" id="barChart">
                @foreach ($grafikBulanan as $data)
                    <div class="chart-bar" style="height: {{ $data['kg'] > 0 ? ($data['kg'] / max(collect($grafikBulanan)->max('kg'), 1) * 100) : 2 }}%;">
                        @if ($data['kg'] > 0)
                            <span class="bar-value">{{ $data['kg'] }}</span>
                        @endif
                        <span class="bar-label">{{ \Carbon\Carbon::create()->month((int)$data['bulan'])->isoFormat('MMM') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SALDO TABUNGAN -->
        <div class="card" style="background: linear-gradient(135deg, #2e7d32, #4caf50); color:#fff;">
            <div style="font-size:13px; opacity:0.85; margin-bottom:8px;">Saldo Tabungan Total</div>
            <div style="font-size:28px; font-weight:700;">{{ formatRupiah($stats['total_saldo']) }}</div>
            <div style="font-size:12px; opacity:0.75; margin-top:6px;">dari {{ $stats['total_nasabah_aktif'] }} nasabah aktif</div>
            @can('Administrator')
            <a href="{{ route('tabungan.index') }}" style="display:inline-block; margin-top:14px; background:rgba(255,255,255,0.2); color:#fff; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:600;">Lihat Detail →</a>
            @endcan
        </div>
    </div>

    <!-- JENIS SAMPAH SUMMARY -->
    <div class="grid-3" style="margin-bottom: 24px;">
        @foreach ($komposisiJenis as $j)
            <div class="card" style="text-align:center;">
                <div style="font-size:36px; margin-bottom:8px;">{{ $j['ikon'] ?? '♻️' }}</div>
                <div style="font-weight:700; font-size:16px;">{{ number_format($j['kg'], 1) }} kg</div>
                <div class="trash-tag {{ strtolower(str_replace(' ', '-', $j['kategori'])) }}" style="margin-top:6px;">{{ $j['kategori'] }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:6px;">{{ formatRupiah($j['nilai']) }}</div>
            </div>
        @endforeach
    </div>
@endsection