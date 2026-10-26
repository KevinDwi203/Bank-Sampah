<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Nasabah;
use App\Models\JenisSampah;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            JenisSampahSeeder::class,
            SettingSeeder::class,
        ]);
    }
}

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'nama' => 'Admin Utama', 'role' => 'Administrator', 'password' => 'admin123'],
            ['username' => 'petugas', 'nama' => 'Budi Santoso', 'role' => 'Petugas', 'password' => 'petugas123'],
            ['username' => 'nasabah', 'nama' => 'Siti Rahayu', 'role' => 'Nasabah', 'password' => 'nasabah123'],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'nama' => $userData['nama'],
                    'role' => $userData['role'],
                    'password' => $userData['password'],
                    'aktif' => true,
                ]
            );
        }
    }
}

class JenisSampahSeeder extends Seeder
{
    public function run(): void
    {
        $jenisSampah = [
            ['nama' => 'Gelas Plastik', 'kode' => 'GP', 'ikon' => '🥤', 'kategori' => 'Plastik', 'harga_asli_kg' => 3000, 'harga_warga_kg' => 1500, 'poin_kg' => 1],
            ['nama' => 'Botol Plastik', 'kode' => 'BP', 'ikon' => '🧴', 'kategori' => 'Plastik', 'harga_asli_kg' => 5000, 'harga_warga_kg' => 1500, 'poin_kg' => 1],
            ['nama' => 'Kardus', 'kode' => 'KD', 'ikon' => '📦', 'kategori' => 'Kertas', 'harga_asli_kg' => 1800, 'harga_warga_kg' => 1300, 'poin_kg' => 1],
            ['nama' => 'Kaleng', 'kode' => 'KL', 'ikon' => '🥫', 'kategori' => 'Logam', 'harga_asli_kg' => 600, 'harga_warga_kg' => 500, 'poin_kg' => 1],
            ['nama' => 'Gebrus', 'kode' => 'GB', 'ikon' => '♻️', 'kategori' => 'Plastik', 'harga_asli_kg' => 650, 'harga_warga_kg' => 500, 'poin_kg' => 1],
            ['nama' => 'Besi 1', 'kode' => 'BE1', 'ikon' => '🔩', 'kategori' => 'Logam', 'harga_asli_kg' => 4500, 'harga_warga_kg' => 2500, 'poin_kg' => 1],
            ['nama' => 'Besi 2', 'kode' => 'BE2', 'ikon' => '🔧', 'kategori' => 'Logam', 'harga_asli_kg' => 3500, 'harga_warga_kg' => 2000, 'poin_kg' => 1],
            ['nama' => 'Duplek', 'kode' => 'DP', 'ikon' => '📦', 'kategori' => 'Kertas', 'harga_asli_kg' => 600, 'harga_warga_kg' => 450, 'poin_kg' => 1],
            ['nama' => 'Kertas HVS', 'kode' => 'HVS', 'ikon' => '📃', 'kategori' => 'Kertas', 'harga_asli_kg' => 1800, 'harga_warga_kg' => 1100, 'poin_kg' => 1],
            ['nama' => 'Kertas Buram', 'kode' => 'KB', 'ikon' => '📰', 'kategori' => 'Kertas', 'harga_asli_kg' => 1100, 'harga_warga_kg' => 500, 'poin_kg' => 1],
            ['nama' => 'Buku', 'kode' => 'BU', 'ikon' => '📚', 'kategori' => 'Kertas', 'harga_asli_kg' => 1300, 'harga_warga_kg' => 1000, 'poin_kg' => 1],
            ['nama' => 'Plastik Keras', 'kode' => 'PK', 'ikon' => '🪣', 'kategori' => 'Plastik', 'harga_asli_kg' => 1200, 'harga_warga_kg' => 800, 'poin_kg' => 1],
            ['nama' => 'Aluminium', 'kode' => 'AL', 'ikon' => '♻️', 'kategori' => 'Logam', 'harga_asli_kg' => 23000, 'harga_warga_kg' => 20000, 'poin_kg' => 1],
            ['nama' => 'Tulang', 'kode' => 'TLG', 'ikon' => '♻️', 'kategori' => 'Lainnya', 'harga_asli_kg' => 0, 'harga_warga_kg' => 0, 'poin_kg' => 1, 'aktif' => false],
            ['nama' => 'Beling', 'kode' => 'BLG', 'ikon' => '♻️', 'kategori' => 'Lainnya', 'harga_asli_kg' => 0, 'harga_warga_kg' => 0, 'poin_kg' => 1, 'aktif' => false],
            ['nama' => 'Karung', 'kode' => 'KRG', 'ikon' => '♻️', 'kategori' => 'Lainnya', 'harga_asli_kg' => 0, 'harga_warga_kg' => 0, 'poin_kg' => 1, 'aktif' => false],
            ['nama' => 'Galon',  'kode' => 'GLN', 'ikon' => '♻️', 'kategori' => 'Lainnya', 'harga_asli_kg' => 0, 'harga_warga_kg' => 0, 'poin_kg' => 1, 'aktif' => false],
        ];

        foreach ($jenisSampah as $data) {
            JenisSampah::updateOrCreate(
                ['nama' => $data['nama']],
                array_merge(['aktif' => true], $data)
            );
        }
    }
}

class NasabahSeeder extends Seeder
{
    public function run(): void
    {
        $nasabahData = [
            ['kode' => 'NK.001', 'nama' => 'Siti Rahayu', 'telepon' => '081234567890', 'alamat' => 'Jl. Mawar No. 12', 'saldo' => 145000, 'poin' => 290, 'status' => 'Aktif'],
            ['kode' => 'NK.002', 'nama' => 'Budi Santoso', 'telepon' => '082345678901', 'alamat' => 'Jl. Melati No. 5', 'saldo' => 87500, 'poin' => 175, 'status' => 'Aktif'],
            ['kode' => 'NK.003', 'nama' => 'Dewi Kusuma', 'telepon' => '083456789012', 'alamat' => 'Jl. Dahlia No. 8', 'saldo' => 210000, 'poin' => 420, 'status' => 'Aktif'],
            ['kode' => 'NK.004', 'nama' => 'Ahmad Fauzi', 'telepon' => '084567890123', 'alamat' => 'Jl. Anggrek No. 3', 'saldo' => 32000, 'poin' => 64, 'status' => 'Aktif'],
            ['kode' => 'NK.005', 'nama' => 'Rina Wulandari', 'telepon' => '085678901234', 'alamat' => 'Jl. Kenanga No. 7', 'saldo' => 0, 'poin' => 0, 'status' => 'Tidak Aktif'],
            ['kode' => 'NK.006', 'nama' => 'Hendra Wijaya', 'telepon' => '086789012345', 'alamat' => 'Jl. Cempaka No. 1', 'saldo' => 165000, 'poin' => 330, 'status' => 'Aktif'],
            ['kode' => 'NK.007', 'nama' => 'Maya Sari', 'telepon' => '087890123456', 'alamat' => 'Jl. Kamboja No. 9', 'saldo' => 54000, 'poin' => 108, 'status' => 'Aktif'],
        ];

        foreach ($nasabahData as $data) {
            Nasabah::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );
        }
    }
}

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'nama_utama', 'value' => 'Bank Sampah', 'type' => 'string', 'group' => 'identitas', 'description' => 'Nama utama bank sampah'],
            ['key' => 'subjudul', 'value' => 'Hijau Lestari', 'type' => 'string', 'group' => 'identitas', 'description' => 'Subjudul/Tagline'],
            ['key' => 'title_kecil', 'value' => '', 'type' => 'string', 'group' => 'identitas', 'description' => 'Tulisan kecil di header'],
            ['key' => 'alamat', 'value' => 'Jl. Raya No. 123, Desa Sukamaju', 'type' => 'string', 'group' => 'identitas', 'description' => 'Alamat lengkap'],
            ['key' => 'telepon', 'value' => '0812-3456-7890', 'type' => 'string', 'group' => 'identitas', 'description' => 'Nomor telepon'],
            ['key' => 'email', 'value' => 'banksampah@example.com', 'type' => 'string', 'group' => 'identitas', 'description' => 'Email'],
            ['key' => 'nomor_rekening', 'value' => '', 'type' => 'string', 'group' => 'identitas', 'description' => 'Nomor rekening bank'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}