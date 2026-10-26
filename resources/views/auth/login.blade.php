<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Bank Sampah Hijau Lestari</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>♻️</text></svg>">
</head>
<body>

<div class="login-wrapper">

    <!-- ======== LEFT PANEL ======== -->
    <div class="login-left">
        <div class="brand-logo">
            <div class="brand-icon">♻️</div>
            <div class="brand-name">
                <h1>{{ Setting::get('nama_utama', 'Bank Sampah') }}</h1>
                <p>{{ Setting::get('subjudul', 'Hijau Lestari') }}</p>
            </div>
        </div>

        <p class="brand-tagline">
            Bersama kami, sampah menjadi nilai.
            Kelola setoran, tabungan, dan laporan bank sampah Anda dengan mudah.
        </p>

        <div class="brand-features">
            <div class="feature-item">
                <span class="feat-icon">📦</span>
                <p>Pencatatan setoran sampah real-time</p>
            </div>
            <div class="feature-item">
                <span class="feat-icon">💰</span>
                <p>Manajemen tabungan & poin nasabah</p>
            </div>
            <div class="feature-item">
                <span class="feat-icon">📊</span>
                <p>Laporan & statistik lengkap</p>
            </div>
            <div class="feature-item">
                <span class="feat-icon">🌱</span>
                <p>Pantau dampak lingkungan bersama</p>
            </div>
        </div>
    </div>

    <!-- ======== RIGHT PANEL ======== -->
    <div class="login-right">
        <div class="login-form-container">
            <div class="login-header">
                <h2>Selamat Datang! 👋</h2>
                <p>Masuk ke akun Bank Sampah Anda</p>
            </div>

            <!-- Error Message -->
            @if ($errors->any())
                <div class="error-message show" id="errorMsg">
                    <span>⚠️</span>
                    <span id="errorText">{{ $errors->first() }}</span>
                </div>
            @else
                <div class="error-message" id="errorMsg">
                    <span>⚠️</span>
                    <span id="errorText">Username atau password salah.</span>
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="username"
                            autocomplete="username"
                            required
                            value="{{ old('username') }}"
                        >
                    </div>
                    @error('username')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password Anda"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword()" id="eyeBtn" aria-label="Tampilkan password">
                            👁️
                        </button>
                    </div>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="remember" id="rememberMe">
                        Ingat saya
                    </label>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">🔑 Masuk Sekarang</span>
                </button>
            </form>

            <div style="margin-top: 18px; padding: 12px 14px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 12px; color: #64748b;">
                <div style="font-weight: 600; color: #334155; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <span>🧪</span> Akun Pengujian (1-Klik untuk Guru):
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" onclick="quickLogin('testadmin', 'admin123')" style="cursor: pointer; background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        Admin Dummy (testadmin)
                    </button>
                    <button type="button" onclick="quickLogin('testpetugas', 'petugas123')" style="cursor: pointer; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                        Petugas Dummy (testpetugas)
                    </button>
                </div>
            </div>

            

            <div class="login-footer">
                © 2026 {{ Setting::get('nama_utama', 'Bank Sampah Hijau Lestari') }} &nbsp;|&nbsp; v2.0.0
            </div>
        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const btn   = document.getElementById('eyeBtn');
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁️';
        }
    }

    function quickLogin(user, pass) {
        document.getElementById('username').value = user;
        document.getElementById('password').value = pass;
        document.getElementById('loginForm').dispatchEvent(new Event('submit'));
    }
</script>

</body>
</html>