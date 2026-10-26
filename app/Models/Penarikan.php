<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasDummyScope;

class Penarikan extends Model
{
    use HasFactory, HasDummyScope;

    protected $table = 'penarikan';

    protected $fillable = [
        'kode',
        'nasabah_id',
        'jumlah',
        'metode',
        'catatan',
        'status',
        'tanggal',
        'is_dummy',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
            'is_dummy' => 'boolean',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
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

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year);
    }

    public function getJumlahFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }
}