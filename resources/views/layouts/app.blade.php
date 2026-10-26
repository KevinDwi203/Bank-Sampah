<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#2e7d32">
    <title>@yield('title', 'Bank Sampah Hijau Lestari')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>♻️</text></svg>">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div class="app-layout" id="appLayout">
        @include('layouts.sidebar')
        
        <div class="main-content" id="mainContent">
            @include('layouts.topbar')
            
            <main class="page-content">
                @foreach (['success' => '#16a34a', 'warning' => '#d97706', 'error' => '#dc2626'] as $type => $color)
                    @if (session($type))
                        <div style="background:{{ $color }}15; border:1px solid {{ $color }}; color:{{ $color }}; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                            {{ session($type) }}
                        </div>
                    @endif
                @endforeach
                @if ($errors->any() && !request()->routeIs('login'))
                    <div style="background:#dc262615; border:1px solid #dc2626; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    
    <script>
        // Global helpers
        window.formatRupiah = function(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        };
        
        window.formatDate = function(dateStr) {
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        };
        
        window.formatShortDate = function(dateStr) {
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        };
        
        window.showToast = function(message, type = 'success', duration = 3000) {
            let toast = document.getElementById('globalToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'globalToast';
                toast.className = 'toast';
                document.body.appendChild(toast);
            }
            toast.className = `toast ${type}`;
            toast.innerHTML = `<span>${type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌'}</span> ${message}`;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), duration);
        };
        
        window.confirmAction = function(message, onConfirm) {
            if (confirm(message)) onConfirm();
        };
        
        window.openModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.classList.add('show');
        };
        
        window.closeModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.classList.remove('show');
        };
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>