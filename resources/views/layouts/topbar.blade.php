<header class="topbar">
    <div class="topbar-left">
        <h1>@yield('pageTitle', 'Dashboard')</h1>
        <p id="topbarDate">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>
    <div class="topbar-right">
        <button class="topbar-btn" title="Notifikasi">
            🔔
            <span class="badge">3</span>
        </button>
        <button class="topbar-btn" title="Bantuan">❓</button>
        <div class="topbar-date">
            🌱 Selamat datang, <strong>{{ auth()->user()->nama }}</strong>
        </div>
    </div>
</header>