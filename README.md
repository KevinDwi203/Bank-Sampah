# Bank Sampah Hijau Lestari - Laravel + MySQL

Aplikasi manajemen Bank Sampah berbasis **Laravel 11** + **MySQL** dengan fitur lengkap:

## 🚀 Fitur Utama

### Manajemen Data
- **Nasabah**: CRUD, status otomatis (Aktif/Tidak Aktif), export CSV/Excel
- **Jenis Sampah**: CRUD dengan **Harga Asli** & **Harga ke Warga** terpisah
- **Transaksi Setoran**: Multi-jenis (batch), snapshot harga, koreksi saldo otomatis
- **Penarikan**: Alur status (Pending → Selesai/Dibatalkan), validasi saldo

### Laporan & PDF
- **Rekap Mingguan**: Detail per nasabah per tanggal
- **Rekap Bulanan**: Pivot per nasabah per minggu (Minggu 1-5)
- **Laporan Kiloan**: Detail setoran per jenis dengan harga asli & warga
- **Export PDF**: Template rapi A4 landscape/portrait dengan header, footer, tanda tangan

### Sistem
- **Role-based Access**: Administrator, Petugas, Nasabah
- **Auth**: Session-based login dengan remember me
- **Settings**: Identitas bank sampah + upload logo
- **Sidebar Collapsible**: Desktop (hover/click) + Mobile (toggle)
- **Dashboard Real-time**: Statistik dari DB, grafik bulanan, komposisi jenis

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js & NPM (untuk Vite)

## 🛠️ Instalasi

```bash
# 1. Clone / copy project
cd "Dev Bank sampah"

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
# Edit .env - konfigurasi database MySQL

# 4. Generate key
php artisan key:generate

# 5. Create database MySQL
mysql -u root -p
CREATE DATABASE bank_sampah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Run migrations & seeders
php artisan migrate --seed

# 7. Storage link (untuk logo upload)
php artisan storage:link

# 8. Build assets
npm run build
# atau development
npm run dev

# 9. Jalankan server
php artisan serve
```

## 🔐 Default Login

| Role | Username | Password |
|------|----------|----------|
| Administrator | admin | admin123 |
| Petugas | petugas | petugas123 |
| Nasabah | nasabah | nasabah123 |

## 📁 Struktur Penting

```
app/
├── Http/Controllers/     # Controllers
├── Models/              # Eloquent Models
├── Services/            # LaporanService
└── Exports/             # Excel Exports

database/
├── migrations/          # migrations fitur inti
└── seeders/             # DatabaseSeeder + 4 seeders

resources/
├── views/               # Blade templates
│   ├── layouts/         # app, sidebar, topbar
│   ├── auth/            # login
│   ├── dashboard/       # dashboard
│   ├── nasabah/         # index, create, edit, show
│   ├── transaksi/       # index, create, show
│   ├── penarikan/       # index, create, show
│   ├── tabungan/        # index, show
│   ├── jenis-sampah/    # index, create, edit
│   ├── laporan/         # index, mingguan, bulanan, kiloan, pdf/
│   ├── setting/         # index
└── css/                 # app.css, sidebar.css, login.css

routes/
├── web.php              # Web routes
├── api.php              # API routes
└── console.php          # Artisan commands
```

## 🔑 Konsep Harga Dual

| Harga | Digunakan Untuk |
|-------|-----------------|
| **Harga Asli** (`harga_asli_kg`) | Laporan internal, rekap ke Bank Sampah Pusat |
| **Harga ke Warga** (`harga_warga_kg`) | Tampilan nasabah, perhitungan tabungan, setoran |

- Keduanya disimpan **permanen** di MySQL (bukan JS array/localStorage)
- Transaksi menyimpan **snapshot** kedua harga saat dibuat
- Transaksi lama **tidak berubah** saat harga diedit
- Admin bisa edit kapan saja via halaman **Harga Sampah**

## 🤖 Status Nasabah Otomatis

```
Aktif          = Ada transaksi 'Selesai' dalam 1 bulan terakhir
Tidak Aktif    = Tidak ada transaksi 'Selesai' 1 bulan terakhir
Otomatis Aktif = Saat nasabah setor lagi (status 'Selesai')
```

Dihitung dari `last_deposit_at` atau transaksi terakhir `Selesai`.

## 📊 Laporan Excel Format

Mengikuti format **Rekapan Bank Sampah Pusat** (4 sheet):
1. **Bulanan** - Pivot nasabah per minggu
2. **Mingguan** - Detail per nasabah per tanggal
4. **Setoran Kiloan** - Detail per item transaksi

## 🖨️ PDF Generation

Menggunakan `barryvdh/laravel-dompdf`:
- Template Blade terpisah di `resources/views/laporan/pdf/`
- Ukuran A4, landscape untuk tabel lebar
- Header berulang, footer tanda tangan, ringkasan total

## 🔧 Development

```bash
# Watch mode (auto-reload)
npm run dev

# Lint
./vendor/bin/pint

# Test
php artisan test
```

## 📝 Catatan Migrasi dari Node.js

| Asal (Node.js) | Tujuan (Laravel) |
|----------------|------------------|
| Express + SQLite | Laravel + MySQL |
| JWT Token | Session + Guard |
| Vanilla JS fetch | Blade + Form Submit |
| Hardcoded dummy data | DatabaseSeeder + Real Query |
| JS Chart | CSS Bar Chart (server-side) |
| localStorage | Database + Session |

## 📄 License

MIT License - Project PA RPL Kelas XII