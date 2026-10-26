<header class="topbar">
    <div class="topbar-left">
        <h1>@yield('pageTitle', 'Dashboard')</h1>
        <p id="topbarDate">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>
    <div class="topbar-right">
        @if (auth()->check() && auth()->user()->is_dummy)
            <div style="background:#fef3c7; border:1px solid #fde68a; color:#92400e; font-size:12px; font-weight:700; padding:5px 12px; border-radius:20px; display:flex; align-items:center; gap:6px;">
                <span>🧪</span> Mode Pengujian (Data Asli Aman)
            </div>
        @endif
        <div class="topbar-date">
            🌱 Selamat datang, <strong>{{ auth()->user()->nama }}</strong>
        </div>
    </div>
</header>