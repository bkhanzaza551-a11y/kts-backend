<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login - KTS Markets</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #D4A843;
            --gold-light: #FDE68A;
            --gold-dark: #B8922E;
            --gold-glow: rgba(212, 168, 67, 0.25);
            --bg-dark: #07090D;
            --card-dark: rgba(14, 18, 24, 0.92);
            --border-dark: rgba(212, 168, 67, 0.2);
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(212, 168, 67, 0.16) 0%, rgba(7, 9, 13, 0) 65%),
                radial-gradient(circle at 85% 90%, rgba(212, 168, 67, 0.05) 0%, rgba(7, 9, 13, 0) 50%),
                linear-gradient(180deg, #090C11 0%, #06070A 100%);
            min-height: 100vh;
            color: #E2E8F0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        .login-card {
            background: var(--card-dark);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-dark);
            border-radius: 24px;
            box-shadow: 
                0 24px 60px rgba(0, 0, 0, 0.75),
                0 0 40px rgba(212, 168, 67, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            padding: 38px 32px;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            border-color: rgba(212, 168, 67, 0.36);
            box-shadow: 
                0 28px 70px rgba(0, 0, 0, 0.8),
                0 0 55px rgba(212, 168, 67, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .brand-logo-container {
            width: 86px;
            height: 86px;
            margin: 0 auto 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo-glow {
            position: absolute;
            inset: -4px;
            border-radius: 26px;
            background: radial-gradient(circle, rgba(212, 168, 67, 0.4) 0%, rgba(212, 168, 67, 0) 70%);
            filter: blur(12px);
            animation: pulse-glow 3s infinite ease-in-out;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.55; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(1.06); }
        }

        .brand-logo-img {
            width: 86px;
            height: 86px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6);
            position: relative;
            z-index: 1;
            object-fit: contain;
        }

        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #FFFFFF;
            margin-bottom: 6px;
        }

        .brand-title-gold {
            background: linear-gradient(135deg, #D4A843 0%, #FDE68A 50%, #B8922E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(212, 168, 67, 0.08);
            border: 1px solid rgba(212, 168, 67, 0.22);
            color: #F0D78C;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .pulse-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 8px #10B981;
        }

        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #94A3B8;
            margin-bottom: 7px;
            letter-spacing: 0.3px;
        }

        .custom-input-group {
            position: relative;
            display: flex;
            align-items: center;
            background: #0B0E13;
            border: 1px solid #1E2530;
            border-radius: 12px;
            transition: all 0.25s ease;
        }

        .custom-input-group:focus-within {
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.16);
            background: #0E1218;
        }

        .input-icon {
            padding: 0 14px;
            color: #64748B;
            font-size: 16px;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-input-group:focus-within .input-icon {
            color: var(--gold-primary);
        }

        .custom-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: #FFFFFF;
            font-size: 14.5px;
            padding: 13px 14px 13px 0;
        }

        .custom-input::placeholder {
            color: #475569;
        }

        .toggle-password {
            background: none;
            border: none;
            color: #64748B;
            padding: 0 14px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--gold-primary);
        }

        .form-check-input {
            background-color: #0B0E13;
            border: 1px solid #2D3748;
            border-radius: 5px;
            width: 17px;
            height: 17px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--gold-primary);
            border-color: var(--gold-primary);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.2);
        }

        .form-check-label {
            font-size: 13px;
            color: #94A3B8;
            cursor: pointer;
            user-select: none;
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4A843 0%, #F59E0B 50%, #B8922E 100%);
            color: #07090C;
            border: none;
            border-radius: 12px;
            padding: 13px 20px;
            font-size: 14.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 24px rgba(212, 168, 67, 0.28);
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #E0B550 0%, #FBBF24 50%, #C49D35 100%);
            color: #000000;
            transform: translateY(-1.5px);
            box-shadow: 0 12px 30px rgba(212, 168, 67, 0.42);
        }

        .btn-gold:active {
            transform: translateY(0);
            box-shadow: 0 4px 14px rgba(212, 168, 67, 0.25);
        }

        .alert-custom-danger {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #FCA5A5;
            border-radius: 12px;
            font-size: 13px;
            padding: 12px 14px;
        }

        .footer-text {
            color: #64748B;
            font-size: 12px;
            text-align: center;
            margin-top: 24px;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <!-- Brand Logo Header -->
            <div class="text-center mb-4">
                <div class="brand-logo-container">
                    <div class="brand-logo-glow"></div>
                    <img src="{{ asset('images/app-logo.png') }}" alt="KTS Markets Logo" class="brand-logo-img">
                </div>

                <h1 class="brand-title">
                    KTS <span class="brand-title-gold">Markets</span>
                </h1>
                
                <div>
                    <span class="admin-badge">
                        <span class="pulse-dot"></span> Super Admin Terminal
                    </span>
                </div>
            </div>

            <!-- Error Alerts -->
            @if($errors->any())
            <div class="alert alert-custom-danger mb-4">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                    <strong>Authentication Failed:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">ADMIN EMAIL</label>
                    <div class="custom-input-group">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="custom-input" id="email" name="email" placeholder="admin@ktsmarkets.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">PASSWORD</label>
                    <div class="custom-input-group">
                        <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="custom-input" id="password" name="password" placeholder="••••••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 pt-1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember this session</label>
                    </div>
                </div>

                <button type="submit" class="btn-gold">
                    <i class="bi bi-lock-fill"></i> Secure Sign In
                </button>
            </form>
        </div>

        <div class="footer-text">
            &copy; {{ date('Y') }} KTS Markets Inc. Institutional Trading Intelligence
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('bi-eye', !isPassword);
                toggleIcon.classList.toggle('bi-eye-slash', isPassword);
            });
        }
    </script>
</body>
</html>
