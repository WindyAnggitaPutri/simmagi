<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMMAGI</title>

    <style>
        /* ===== Scaling Factor ===== */
        :root {
            --s: 1;
        }

        @media (max-width: 480px) {
            :root { --s: 0.75; }
        }

        @media (max-width: 380px) {
            :root { --s: 0.62; }
        }

        /* ===== Reset ===== */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ===== Body ===== */
        body {
            background-color: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        /* ===== Wrapper ===== */
        .login-wrapper {
            width: 100%;
            max-width: 448px;
            padding: 0 16px;
        }

        /* Mobile: max-width DIPERKECIL + padding DIBESARKAN */
        @media (max-width: 480px) {
            .login-wrapper {
                max-width: 380px;
                padding: 0 32px;
            }
        }

        @media (max-width: 380px) {
            .login-wrapper {
                max-width: 340px;
                padding: 0 28px;
            }
        }

        /* ===== Card ===== */
        .login-card {
            background: #ffffff;
            border: 1px solid #fef3c7;
            border-radius: calc(8px * var(--s));
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            padding: calc(32px * var(--s));
        }

        /* ===== Brand ===== */
        .brand-section {
            text-align: center;
            margin-bottom: calc(24px * var(--s));
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .brand-logo {
            height: calc(40px * var(--s));
        }

        .brand-title {
            font-size: calc(24px * var(--s));
            font-weight: 700;
            margin-top: calc(12px * var(--s));
            color: #1f2937;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: calc(14px * var(--s));
            color: #6b7280;
            line-height: 1.4;
        }

        /* ===== Heading ===== */
        .login-heading {
            font-size: calc(18px * var(--s));
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: calc(24px * var(--s));
            line-height: 1.3;
        }

        /* ===== Link Kembali (TANPA KOTAK) ===== */
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: calc(4px * var(--s));
            width: 100%;
            font-weight: 500;
            font-size: calc(13px * var(--s));
            color: #6b7280;
            background: transparent;
            border: none;
            padding: calc(8px * var(--s)) 0;
            text-decoration: none;
            transition: color 0.2s;
            margin-top: calc(12px * var(--s));
        }

        .btn-back:hover {
            color: #374151;
        }

        .btn-back svg {
            width: calc(14px * var(--s));
            height: calc(14px * var(--s));
        }

        /* ===== Form ===== */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: calc(20px * var(--s));
        }

        .form-group label {
            display: block;
            font-size: calc(14px * var(--s));
            font-weight: 500;
            color: #4b5563;
            line-height: 1.4;
        }

        .form-input {
            width: 100%;
            margin-top: calc(4px * var(--s));
            padding: calc(8px * var(--s)) calc(16px * var(--s));
            border: 1px solid #e5e7eb;
            border-radius: calc(4px * var(--s));
            outline: none;
            font-family: inherit;
            font-size: calc(14px * var(--s));
            color: #1f2937;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4);
        }

        /* ===== Password ===== */
        .password-wrapper {
            position: relative;
            margin-top: calc(4px * var(--s));
        }

        .password-input {
            width: 100%;
            padding: calc(8px * var(--s)) calc(40px * var(--s)) calc(8px * var(--s)) calc(16px * var(--s));
            border: 1px solid #e5e7eb;
            border-radius: calc(4px * var(--s));
            outline: none;
            font-family: inherit;
            font-size: calc(14px * var(--s));
            color: #1f2937;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .password-input::placeholder {
            color: #9ca3af;
        }

        .password-input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4);
        }

        .toggle-password {
            position: absolute;
            right: calc(12px * var(--s));
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: calc(4px * var(--s));
        }

        .toggle-password:hover {
            color: #4b5563;
        }

        .toggle-password svg {
            width: calc(20px * var(--s));
            height: calc(20px * var(--s));
        }

        .is-hidden {
            display: none;
        }

        /* ===== Remember & Forgot ===== */
        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: calc(14px * var(--s));
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: calc(8px * var(--s));
            color: #4b5563;
            cursor: pointer;
        }

        .remember-label input[type="checkbox"] {
            accent-color: #eab308;
            width: max(14px, calc(16px * var(--s)));
            height: max(14px, calc(16px * var(--s)));
        }

        .forgot-link {
            color: #ca8a04;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #a16207;
        }

        /* ===== Button ===== */
        .btn-login {
            width: 100%;
            background: #eab308;
            color: #ffffff;
            font-weight: 700;
            font-size: calc(16px * var(--s));
            padding: calc(8px * var(--s)) 0;
            border: none;
            border-radius: calc(4px * var(--s));
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: background 0.2s;
            font-family: inherit;
        }

        .btn-login:hover {
            background: #ca8a04;
        }

        /* ===== Alerts ===== */
        .error-alert {
            margin-top: calc(16px * var(--s));
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            font-size: calc(14px * var(--s));
            padding: calc(12px * var(--s));
            border-radius: calc(4px * var(--s));
            line-height: 1.5;
        }

        .success-alert {
            margin-top: calc(16px * var(--s));
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
            font-size: calc(14px * var(--s));
            padding: calc(12px * var(--s));
            border-radius: calc(4px * var(--s));
            display: flex;
            align-items: center;
            gap: calc(8px * var(--s));
            transition: opacity 0.5s;
            line-height: 1.5;
        }

        .success-alert svg {
            width: calc(20px * var(--s));
            height: calc(20px * var(--s));
            flex-shrink: 0;
        }

        /* ===== Footer ===== */
        .footer-text {
            text-align: center;
            font-size: calc(12px * var(--s));
            color: #9ca3af;
            margin-top: calc(24px * var(--s));
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- Card Login -->
        <div class="login-card">
            <div class="brand-section">
                <img src="{{ asset('images/logo.png') }}" 
                    alt="Logo" 
                    class="brand-logo">
                
                <h1 class="brand-title">
                    SIMMAGI
                </h1>
                
                <p class="brand-subtitle">
                    Sistem Monitoring Magang
                </p>
            </div>

            <h2 class="login-heading">
                Login ke Dashboard
            </h2>

            <!-- FORM -->
            <form action="{{ route('login.post') }}" method="POST" class="login-form">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="Masukkan username"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password-input">Password</label>
                    <div class="password-wrapper">
                        <input 
                            id="password-input"
                            type="password" 
                            name="password"
                            class="password-input"
                            placeholder="Masukkan password"
                            required
                        >
                        
                        <!-- Tombol Mata -->
                        <button 
                            type="button"
                            id="toggle-password"
                            class="toggle-password"
                        >
                            <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            
                            <svg id="icon-eye-off" class="is-hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember & Lupa -->
                <div class="remember-forgot">
                    <label class="remember-label">
                        <input type="checkbox" name="remember">
                        Ingat saya
                    </label>

                    <a href="{{ route('forgot.password') }}" class="forgot-link">
                        Lupa Password?
                    </a>
                </div>

                <!-- Button -->
                <button 
                    type="submit"
                    class="btn-login"
                >
                    Masuk
                </button>

            </form>

            <!-- LINK KEMBALI KE BERANDA (DIBAWAH TOMBOL MASUK) -->
            <a href="{{ route('beranda_pengunjung') }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Beranda
            </a>

            <!-- Error -->
            @if ($errors->any())
                <div class="error-alert">
                    {{ $errors->first() }}
                </div>
            @endif
            
            @if (session('success'))
                <div id="success-alert" class="success-alert">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

        </div>

        <!-- Footer -->
        <p class="footer-text">
            © {{ date('Y') }} SIMMAGI
        </p>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Toggle Password
            document.getElementById('toggle-password').addEventListener('click', function() {
                const passwordInput = document.getElementById('password-input');
                const iconEye = document.getElementById('icon-eye');
                const iconEyeOff = document.getElementById('icon-eye-off');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    iconEye.classList.add('is-hidden');
                    iconEyeOff.classList.remove('is-hidden');
                } else {
                    passwordInput.type = 'password';
                    iconEye.classList.remove('is-hidden');
                    iconEyeOff.classList.add('is-hidden');
                }
            });

            // Auto-hide Alert Success (5 detik)
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.style.opacity = '0';
                    setTimeout(function() {
                        successAlert.style.display = 'none';
                    }, 500);
                }, 5000);
            }

        });
    </script>
</body>
</html>