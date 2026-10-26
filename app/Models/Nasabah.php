<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nasabah extends Model
{
    use HasFactory;

    protected $table = 'nasabah';

    protected $fillable = [
        'kode',
        'nama',
        'telepon',
        'alamat',
        'saldo',
        'poin',
        'status',
        'last_deposit_at',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'poin' => 'integer',
            'last_deposit_at' => 'date',
        ];
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function penarikan(): HasMany
    {
        return $this->hasMany(Penarikan::class);
    }

    public function transaksiSelesai(): HasMany
    {
        return $this->hasMany(Transaksi::class)->where('status', 'Selesai');
    }

    public function penarikanSelesai(): HasMany
    {
        return $this->hasMany(Penarikan::class)->where('status', 'Selesai');
    }

    public function updateStatusOtomatis(): void
    {
        $lastDeposit = $this->transaksiSelesai()->latest('tanggal')->first();

        if ($lastDeposit) {
            $this->last_deposit_at = $lastDeposit->tanggal;
            $this->status = $lastDeposit->tanggal >= now()->subMonth() ? 'Aktif' : 'Tidak Aktif';
        } else {
            $this->status = $this->created_at < now()->subMonth() ? 'Tidak Aktif' : 'Aktif';
            $this->last_deposit_at = null;
        }
        $this->save();
    }

    public static function perbaruiStatusJikaPerlu(): void
    {
        if (! \Illuminate\Support\Facades\Cache::add('nasabah_status_diperbarui', true, now()->addHours(6))) {
            return;
        }

        static::query()->get()->each->updateStatusOtomatis();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function scopeTidakAktif($query)
    {
        return $query->where('status', 'Tidak Aktif');
    }

    public function getTotalSaldoAttribute(): float
    {
        return $this->saldo;
    }

    public function getTotalPoinAttribute(): int
    {
        return $this->poin;
    }
}