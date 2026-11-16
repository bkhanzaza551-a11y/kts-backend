<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - KTS Markets</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-primary: #D4A843;
            --gold-light: #FDE68A;
            --gold-dark: #B8922E;
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
                linear-gradient(180deg, #090C11 0%, #06070A 100%);
            min-height: 100vh;
            color: #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 24px;
        }

        .auth-card {
            background: var(--card-dark);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-dark);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.75), 0 0 40px rgba(212, 168, 67, 0.08);
            padding: 38px 32px;
        }

        .icon-badge-glow {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(212, 168, 67, 0.2), rgba(212, 168, 67, 0.05));
            border: 1.5px solid rgba(212, 168, 67, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 25px rgba(212, 168, 67, 0.2);
        }

        .brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #FFFFFF;
        }

        .otp-input {
            background: #0B0E13 !important;
            border: 1.5px solid #262D38 !important;
            color: #FDE68A !important;
            font-family: 'JetBrains Mono', monospace !important;
            letter-spacing: 10px;
            font-size: 28px !important;
            text-align: center;
            border-radius: 14px !important;
            padding: 14px !important;
            transition: all 0.25s ease;
        }

        .otp-input:focus {
            border-color: var(--gold-primary) !important;
            box-shadow: 0 0 0 4px rgba(212, 168, 67, 0.2) !important;
            background: #0E1218 !important;
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4A843 0%, #F59E0B 50%, #B8922E 100%);
            color: #07090C;
            border: none;
            border-radius: 12px;
            padding: 13px 20px;
            font-size: 15px;
            font-weight: 700;
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
            transform: translateY(-1.5px);
            box-shadow: 0 12px 30px rgba(212, 168, 67, 0.42);
            color: #000;
        }

        .test-otp-box {
            background: rgba(212, 168, 67, 0.1);
            border: 1px solid rgba(212, 168, 67, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="icon-badge-glow">
                    <i class="bi bi-shield-lock fs-1" style="color: var(--gold-primary);"></i>
                </div>
                <h2 class="brand-title">Two-Factor Authentication</h2>
                <p class="text-secondary small mb-0">Enter the 6-digit OTP verification code</p>
            </div>

            @if(!empty($latestOtp))
            <div class="test-otp-box text-center">
                <div style="color: #94A3B8; font-size: 11.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Current System OTP Code</div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 26px; font-weight: 800; color: #FDE68A; letter-spacing: 6px; margin-top: 4px;">{{ $latestOtp }}</div>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success border-0 py-2 small mb-3" style="background: rgba(16, 185, 129, 0.15); color: #34D399;">
                <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger border-0 py-2 small mb-3" style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5;">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.otp.verify.post') }}">
                @csrf
                <div class="mb-4">
                    <input type="text" name="otp" value="{{ old('otp', $latestOtp ?? '') }}" class="form-control otp-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" autofocus required placeholder="••••••">
                </div>
                <button type="submit" class="btn-gold">
                    <i class="bi bi-check2-circle"></i> Verify & Continue
                </button>
            </form>

            <div class="text-center mt-3 pt-2">
                <form method="POST" action="{{ route('admin.otp.resend') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none p-0 small" style="color: #94A3B8; font-size: 12.5px;">
                        <i class="bi bi-arrow-clockwise me-1"></i>Resend OTP Code
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('admin.login') }}" class="text-decoration-none small" style="color: #64748B;">
                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
            </a>
        </div>
    </div>
</body>
</html>
