<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            @php($logoPath = Setting::get('logo_path'))
            <div class="logo-icon" @if ($logoPath) style="background:#fff; overflow:hidden; padding:3px;" @endif>
                @if ($logoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath))
                    <img src="{{ asset('storage/' . $logoPath) }}?v={{ \Illuminate\Support\Facades\Storage::disk('public')->lastModified($logoPath) }}"
                         alt="Logo" style="width:100%; height:100%; object-fit:contain;">
                @else
                    ♻️
                @endif
            </div>
            <div class="logo-text">
                <h2>{{ Setting::get('nama_utama', 'Bank Sampah') }}</h2>
                <span>{{ Setting::get('subjudul', 'Hijau Lestari') }}</span>
            </div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">👤</div>
        <div class="user-info">
            <h4><a href="{{ route('profile') }}" style="color:inherit; text-decoration:none;" title="Profil saya">{{ auth()->user()->nama }}</a></h4>
            <span>{{ auth()->user()->role }}</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        @if (auth()->user()->isNasabah())
            <p class="nav-section-title">Menu</p>
            <li class="nav-item {{ request()->routeIs('akun.saya') ? 'active' : '' }}">
                <a href="{{ route('akun.saya') }}">
                    <span class="nav-icon">🏠</span> Dashboard
                </a>
            </li>
        @endif

        @can('Petugas')
        <p class="nav-section-title">Menu Utama</p>
        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}"><span class="nav-icon">🏠</span> Dashboard</a>
        </li>
        <li class="nav-item {{ request()->routeIs('riwayat.create') ? 'active' : '' }}">
            <a href="{{ route('riwayat.create') }}"><span class="nav-icon">📦</span> Setor Sampah</a>
        </li>
        <li class="nav-item {{ request()->routeIs('nasabah.*') ? 'active' : '' }}">
            <a href="{{ route('nasabah.index') }}"><span class="nav-icon">👥</span> Data Nasabah</a>
        </li>
        <li class="nav-item {{ request()->routeIs('riwayat.*') && !request()->routeIs('riwayat.create') ? 'active' : '' }}">
            <a href="{{ route('riwayat.index') }}"><span class="nav-icon">📋</span> Riwayat Transaksi</a>
        </li>
        <li class="nav-item {{ request()->routeIs('arus-kas.*') ? 'active' : '' }}">
            <a href="{{ route('arus-kas.index') }}"><span class="nav-icon">💵</span> Arus Kas</a>
        </li>
        @endcan

        @can('Administrator')
        <p class="nav-section-title">Keuangan</p>
        <li class="nav-item {{ request()->routeIs('tabungan.*') ? 'active' : '' }}">
            <a href="{{ route('tabungan.index') }}"><span class="nav-icon">💰</span> Tabungan</a>
        </li>
        <li class="nav-item {{ request()->routeIs('penarikan.*') ? 'active' : '' }}">
            <a href="{{ route('penarikan.index') }}"><span class="nav-icon">🏧</span> Transaksi</a>
        </li>

        <p class="nav-section-title">Laporan</p>
        <li class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.index') }}"><span class="nav-icon">📊</span> Laporan</a>
        </li>
        <li class="nav-item {{ request()->routeIs('jenis-sampah.*') ? 'active' : '' }}">
            <a href="{{ route('jenis-sampah.index') }}"><span class="nav-icon">🏷️</span> Harga Sampah</a>
        </li>

        <p class="nav-section-title">Sistem</p>
        <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}"><span class="nav-icon">🔑</span> Pengguna</a>
        </li>
        <li class="nav-item {{ request()->routeIs('setting.*') ? 'active' : '' }}">
            <a href="{{ route('setting.index') }}"><span class="nav-icon">⚙️</span> Pengaturan</a>
        </li>
        @endcan
    </nav>

    <div class="sidebar-footer">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
            <span>🚪</span> Keluar
        </a>
        <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</aside>

<!-- Sidebar Toggle Button (Mobile) -->
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
    ☰
</button>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>