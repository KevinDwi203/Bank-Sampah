<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSampah extends Model
{
    use HasFactory;

    protected $table = 'jenis_sampah';

    protected $fillable = [
        'nama',
        'kode',
        'ikon',
        'kategori',
        'harga_asli_kg',
        'harga_warga_kg',
        'poin_kg',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_asli_kg' => 'decimal:2',
            'harga_warga_kg' => 'decimal:2',
            'poin_kg' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'jenis_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function getHargaAsliFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_asli_kg, 0, ',', '.');
    }

    public function getHargaWargaFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_warga_kg, 0, ',', '.');
    }
}