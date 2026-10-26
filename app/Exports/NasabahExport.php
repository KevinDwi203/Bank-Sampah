<?php

namespace App\Exports;

use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NasabahExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Nasabah::query();

        if (!empty($this->filters['q'])) {
            $search = $this->filters['q'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('kode')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Nasabah',
            'Nama',
            'Telepon',
            'Alamat',
            'Saldo',
            'Poin',
            'Status',
            'Setoran Terakhir',
        ];
    }

    public function map($nasabah): array
    {
        return [
            $nasabah->kode,
            $nasabah->nama,
            $nasabah->telepon,
            $nasabah->alamat,
            $nasabah->saldo,
            $nasabah->poin,
            $nasabah->status,
            $nasabah->last_deposit_at?->format('Y-m-d') ?? '-',
        ];
    }
}