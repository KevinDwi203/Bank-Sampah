<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasDummyScope;

class Transaksi extends Model
{
    use HasFactory, HasDummyScope;

    protected $table = 'transaksi';

    protected $fillable = [
        'kode',
        'nasabah_id',
        'jenis_id',
        'berat_kg',
        'harga_asli_kg',
        'harga_warga_kg',
        'total',
        'poin_dapat',
        'catatan',
        'status',
        'tanggal',
        'is_dummy',
    ];

    protected function casts(): array
    {
        return [
            'berat_kg' => 'decimal:3',
            'harga_asli_kg' => 'decimal:2',
            'harga_warga_kg' => 'decimal:2',
            'total' => 'decimal:2',
            'poin_dapat' => 'integer',
            'tanggal' => 'date',
            'is_dummy' => 'boolean',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_id');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'Selesai');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeDibatalkan($query)
    {
        return $query->where('status', 'Dibatalkan');
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
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

    public function scopeMingguIni($query)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        return $query->whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
    }

    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    public function getBeratFormattedAttribute(): string
    {
        return number_format($this->berat_kg, 2, ',', '.') . ' kg';
    }
}