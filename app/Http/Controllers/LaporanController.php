<?php

namespace App\Http\Controllers;

use App\Services\LaporanService;
use App\Models\Transaksi;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $laporan = $this->laporanService->getLaporanBulanan($bulan, $tahun);
        $mingguan = $this->laporanService->getLaporanMingguan($bulan, $tahun);
        $dari = sprintf('%04d-%02d-01', $tahun, $bulan);
        $sampai = date('Y-m-t', strtotime($dari));
        $rekap = $this->laporanService->getRekapPerJenisAsli($dari, $sampai);

        return view('laporan.index', compact('laporan', 'mingguan', 'rekap', 'bulan', 'tahun'));
    }

    public function mingguan(Request $request)
    {
        $dari = $request->get('dari', now()->startOfWeek()->format('Y-m-d'));
        $sampai = $request->get('sampai', now()->endOfWeek()->format('Y-m-d'));

        $data = $this->laporanService->getRekapMingguan($dari, $sampai);

        return view('laporan.mingguan', compact('data', 'dari', 'sampai'));
    }

    public function bulanan(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $data = $this->laporanService->getLaporanBulananDetail($bulan, $tahun);

        return view('laporan.bulanan', compact('data', 'bulan', 'tahun'));
    }

    public function kiloan(Request $request)
    {
        $dari = $request->get('dari', now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->get('sampai', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->laporanService->getLaporanKiloan($dari, $sampai);

        return view('laporan.kiloan', compact('data', 'dari', 'sampai'));
    }

    /**
     * PDF rekapitulasi sampah per jenis (harga asli), tanpa nama nasabah.
     * Periode: ?dari=&sampai= atau ?bulan=&tahun=
     */
    public function pdfRekap(Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date',
            'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:2000,2100',
        ]);

        if ($request->filled('dari') && $request->filled('sampai')) {
            $dari = date('Y-m-d', strtotime($request->dari));
            $sampai = date('Y-m-d', strtotime($request->sampai));
            if ($sampai < $dari) {
                [$dari, $sampai] = [$sampai, $dari];
            }
        } else {
            $bulan = (int) $request->get('bulan', now()->month);
            $tahun = (int) $request->get('tahun', now()->year);
            $dari = sprintf('%04d-%02d-01', $tahun, $bulan);
            $sampai = date('Y-m-t', strtotime($dari));
        }

        $rekap = $this->laporanService->getRekapPerJenisAsli($dari, $sampai);

        $pdf = Pdf::loadView('laporan.pdf.rekap', compact('rekap', 'dari', 'sampai'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("rekap-sampah-{$dari}-sd-{$sampai}.pdf");
    }

    public function pdfMingguan(Request $request)
    {
        $dari = $request->get('dari', now()->startOfWeek()->format('Y-m-d'));
        $sampai = $request->get('sampai', now()->endOfWeek()->format('Y-m-d'));

        $data = $this->laporanService->getRekapMingguan($dari, $sampai);

        $pdf = Pdf::loadView('laporan.pdf.mingguan', compact('data', 'dari', 'sampai'))
            ->setPaper('A4', 'landscape');

        return $pdf->download("laporan-mingguan-{$dari}-sd-{$sampai}.pdf");
    }

    public function pdfBulanan(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        $data = $this->laporanService->getLaporanBulananDetail($bulan, $tahun);

        $pdf = Pdf::loadView('laporan.pdf.bulanan', compact('data', 'bulan', 'tahun'))
            ->setPaper('A4', 'landscape');

        return $pdf->download("laporan-bulanan-{$tahun}-{$bulan}.pdf");
    }

    public function pdfKiloan(Request $request)
    {
        $dari = $request->get('dari', now()->startOfMonth()->format('Y-m-d'));
        $sampai = $request->get('sampai', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->laporanService->getLaporanKiloan($dari, $sampai);

        $pdf = Pdf::loadView('laporan.pdf.kiloan', compact('data', 'dari', 'sampai'))
            ->setPaper('A4', 'landscape');

        return $pdf->download("laporan-kiloan-{$dari}-sd-{$sampai}.pdf");
    }

}
