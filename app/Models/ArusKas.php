<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasDummyScope;

class ArusKas extends Model
{
    use HasFactory, HasDummyScope;

    protected $table = 'arus_kas';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'kategori',
        'masuk',
        'keluar',
        'saldo',
        'catatan',
        'user_id',
        'is_dummy',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'masuk' => 'decimal:2',
            'keluar' => 'decimal:2',
            'saldo' => 'decimal:2',
            'is_dummy' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePeriode($query, $dari, $sampai)
    {
        return $query->whereBetween('tanggal', [$dari, $sampai]);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year);
    }

    public function getMasukFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->masuk, 0, ',', '.');
    }

    public function getKeluarFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->keluar, 0, ',', '.');
    }

    public function getSaldoFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->saldo, 0, ',', '.');
    }

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'pengeluaran_operasional' => 'Pengeluaran Operasional',
            'pembayaran_pengurus' => 'Pembayaran Pengurus',
            'pembelian_peralatan' => 'Pembelian Peralatan',
            'biaya_transport' => 'Biaya Transport',
            'biaya_konsumsi' => 'Biaya Konsumsi',
            'lainnya' => 'Lainnya',
            default => $this->kategori,
        };
    }
}