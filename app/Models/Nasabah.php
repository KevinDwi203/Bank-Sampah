<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Traits\HasDummyScope;

class Nasabah extends Model
{
    use HasFactory, HasDummyScope;

    protected $table = 'nasabah';

    protected $fillable = [
        'kode',
        'nomor_kartu',
        'nama',
        'telepon',
        'alamat',
        'saldo',
        'poin',
        'status',
        'last_deposit_at',
        'is_dummy',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'poin' => 'integer',
            'last_deposit_at' => 'date',
            'is_dummy' => 'boolean',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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

        if ($lastDeposit && $lastDeposit->tanggal) {
            $this->last_deposit_at = $lastDeposit->tanggal;
            $tanggalDeposit = \Carbon\Carbon::parse($lastDeposit->tanggal)->startOfDay();
            $batasAktif = now()->subMonth()->startOfDay();
            $this->status = $tanggalDeposit->greaterThanOrEqualTo($batasAktif) ? 'Aktif' : 'Tidak Aktif';
        } else {
            $this->status = 'Tidak Aktif';
            $this->last_deposit_at = null;
        }
        $this->save();
    }

    public static function perbaruiStatusJikaPerlu(bool $force = false): void
    {
        if (! $force && ! \Illuminate\Support\Facades\Cache::add('nasabah_status_diperbarui', true, now()->addMinutes(15))) {
            return;
        }

        static::with('transaksiSelesai')->get()->each->updateStatusOtomatis();
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