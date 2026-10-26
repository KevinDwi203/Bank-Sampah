<header class="topbar">
    <div class="topbar-left">
        <h1>@yield('pageTitle', 'Dashboard')</h1>
        <p id="topbarDate">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
    </div>
    <div class="topbar-right">
        <div class="topbar-date">
            🌱 Selamat datang, <strong>{{ auth()->user()->nama }}</strong>
        </div>
    </div>
</header>