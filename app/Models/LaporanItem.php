<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanItem extends Model
{
    use HasFactory;

    protected $table = 'laporan_items';

    protected $fillable = [
        'laporan_id',
        'jenis_id',
        'nama_jenis',
        'berat_kg',
        'harga_kg',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'berat_kg' => 'decimal:3',
            'harga_kg' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(Laporan::class);
    }

    public function jenisSampah(): BelongsTo
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_id');
    }
}
