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

        $nasabahAccounts = [
            ['card' => '01', 'nama' => 'Ibu Lilis', 'username' => 'nk01', 'password' => 'QnUAA5IH'],
            ['card' => '02', 'nama' => 'Bu Aep', 'username' => 'nk02', 'password' => 'xgtiNfL7'],
            ['card' => '03', 'nama' => 'Wa Deden', 'username' => 'nk03', 'password' => '0R9bPXic'],
            ['card' => '04', 'nama' => 'Bu Ahmad', 'username' => 'nk04', 'password' => 'gRvBXDsi'],
            ['card' => '05', 'nama' => 'Pak Iwan Dermawan', 'username' => 'nk05', 'password' => 'ydderAey'],
            ['card' => '06', 'nama' => 'Iwan Nirwana', 'username' => 'nk06', 'password' => 'tob3bt90'],
            ['card' => '07', 'nama' => 'Gilang N', 'username' => 'nk07', 'password' => '5x4ZMH16'],
            ['card' => '08', 'nama' => 'Pak Martin', 'username' => 'nk08', 'password' => 'lYxLf6Od'],
            ['card' => '09', 'nama' => 'Teh Meli', 'username' => 'nk09', 'password' => '8m4zVERo'],
            ['card' => '10', 'nama' => 'Bu Soemantri', 'username' => 'nk10', 'password' => 'oZ77oEgj'],
            ['card' => '11', 'nama' => 'Pak Heru', 'username' => 'nk11', 'password' => 'hFL5Qr2e'],
            ['card' => '12', 'nama' => 'Kaka Ica', 'username' => 'nk12', 'password' => 'ewb4gYuA'],
            ['card' => '13', 'nama' => 'Bunda/Mamah Meimei', 'username' => 'nk13', 'password' => 'hMyapFc5'],
            ['card' => '14', 'nama' => 'Bu Momor', 'username' => 'nk14', 'password' => 'htQLPlVG'],
            ['card' => '15', 'nama' => 'Pak Adang', 'username' => 'nk15', 'password' => 'kk6NeiLa'],
            ['card' => '16', 'nama' => 'Mang Ade', 'username' => 'nk16', 'password' => 'ZeVU309u'],
            ['card' => '17', 'nama' => 'Bu Hjh. Endang', 'username' => 'nk17', 'password' => 'paLVbA7M'],
            ['card' => '18', 'nama' => 'Pak Deden', 'username' => 'nk18', 'password' => '6q4pCTzh'],
            ['card' => '19', 'nama' => 'Bu Yuda', 'username' => 'nk19', 'password' => 'KA3xs4LO'],
            ['card' => '20', 'nama' => 'Mamah Kekey', 'username' => 'nk20', 'password' => 'pCt5JKcZ'],
            ['card' => '21', 'nama' => 'Bu Eko', 'username' => 'nk21', 'password' => 'kaAevyOV'],
            ['card' => '22', 'nama' => 'Bu Dede GH', 'username' => 'nk22', 'password' => 'ynQOcvqV'],
        ];

        foreach ($nasabahAccounts as $acc) {
            $nasabah = Nasabah::where('nomor_kartu', $acc['card'])
                ->orWhere('kode', 'like', "%{$acc['card']}")
                ->first();

            User::updateOrCreate(
                ['username' => $acc['username']],
                [
                    'nama' => $acc['nama'],
                    'role' => 'Nasabah',
                    'password' => $acc['password'],
                    'aktif' => true,
                    'nasabah_id' => $nasabah?->id,
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