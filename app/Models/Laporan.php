<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasDummyScope;

class Laporan extends Model
{
    use HasFactory, HasDummyScope;

    protected $table = 'laporan';

    protected $fillable = [
        'kode',
        'tanggal',
        'keterangan',
        'total_berat_kg',
        'total_nilai',
        'catatan',
        'user_id',
        'is_dummy',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_berat_kg' => 'decimal:3',
            'total_nilai' => 'decimal:2',
            'is_dummy' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaporanItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
