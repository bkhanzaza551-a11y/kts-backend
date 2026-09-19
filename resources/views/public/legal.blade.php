<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - KTS Markets Official Legal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --kts-gold: #D4A843;
            --kts-gold-light: #F0D78C;
            --kts-bg: #07090C;
            --kts-card: #0D1117;
            --kts-border: rgba(255, 255, 255, 0.08);
            --kts-text-muted: #94A3B8;
        }
        body {
            background-color: var(--kts-bg);
            color: #E2E8F0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.7;
            position: relative;
            min-height: 100vh;
        }
        .bg-glow {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1000px;
            height: 400px;
            background: radial-gradient(circle, rgba(212,168,67,0.08) 0%, rgba(7,9,12,0) 70%);
            pointer-events: none;
            z-index: 0;
        }
        .navbar-custom {
            background: rgba(7, 9, 12, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--kts-border);
        }
        .legal-card {
            background-color: var(--kts-card);
            border: 1px solid var(--kts-border);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: #FFFFFF;
        }
        .text-gold { color: var(--kts-gold) !important; }
        .text-gold-gradient {
            background: linear-gradient(135deg, var(--kts-gold), #FDE68A);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .badge-compliance {
            background: rgba(212, 168, 67, 0.12);
            color: var(--kts-gold-light);
            border: 1px solid rgba(212, 168, 67, 0.25);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .legal-content h2 {
            font-size: 1.5rem;
            color: var(--kts-gold);
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--kts-border);
        }
        .legal-content h3 {
            font-size: 1.2rem;
            color: #FFFFFF;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .legal-content h4 {
            font-size: 1rem;
            color: var(--kts-gold-light);
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .legal-content ul, .legal-content ol {
            padding-left: 1.5rem;
            color: #CBD5E1;
            margin-bottom: 1rem;
        }
        .legal-content li {
            margin-bottom: 0.4rem;
        }
        .legal-content p {
            color: var(--kts-text-muted);
            margin-bottom: 1rem;
        }
        .legal-content strong {
            color: #FFFFFF;
        }
        .legal-content a {
            color: var(--kts-gold);
            text-decoration: underline;
        }
        .legal-content a:hover {
            color: var(--kts-gold-light);
        }
        .banner-deletion {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(212, 168, 67, 0.05) 100%);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 14px;
            padding: 16px 20px;
        }
        .btn-delete-req {
            background-color: #EF4444;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 8px 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: opacity 0.2s;
        }
        .btn-delete-req:hover {
            opacity: 0.9;
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <!-- Top Navigation -->
    <nav class="navbar navbar-custom sticky-top py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="/">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded">KTS</span>
                <span class="fw-bold text-light tracking-wide">KTS MARKETS</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('public.delete-account') }}" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 px-3">
                    <i class="bi bi-trash3"></i> Delete Account
                </a>
                <a href="https://play.google.com/store/apps/details?id=com.ktsmarkets" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                    <i class="bi bi-google-play"></i> Get App
                </a>
            </div>
        </div>
    </nav>

    <!-- Content Container -->
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                
                <!-- Compliance Notice Card -->
                <div class="banner-deletion mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger bg-opacity-25 text-danger p-2 rounded-3">
                            <i class="bi bi-shield-check fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-light small">Google Play Store & GDPR Compliant</div>
                            <div class="text-secondary small">End-to-End Encryption • Strict 18+ Policy • Transparent Data Deletion</div>
                        </div>
                    </div>
                    <a href="{{ route('public.delete-account') }}" class="btn-delete-req">
                        <i class="bi bi-trash3-fill"></i> Web Deletion Portal
                    </a>
                </div>

                <!-- Main Legal Card -->
                <div class="legal-card p-4 p-md-5">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                        <div>
                            <div class="badge-compliance d-inline-block mb-2">
                                <i class="bi bi-patch-check-fill text-success me-1"></i> VERIFIED OFFICIAL DOCUMENTATION
                            </div>
                            <h1 class="h2 text-gold-gradient mb-1">{{ $title }}</h1>
                            <p class="text-secondary small mb-0">
                                Application: <code class="text-warning bg-dark px-2 py-1 rounded">com.ktsmarkets</code> • Last updated: {{ $updated_at->format('F d, Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Rendered HTML Content -->
                    <div class="legal-content">
                        {!! strip_tags($content, '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><blockquote><pre><code><table><thead><tbody><tr><th><td><hr><span><div>') !!}
                    </div>

                    <!-- Footer Risk Disclaimer -->
                    <div class="p-3 rounded-3 mt-5 bg-black border border-warning border-opacity-25">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            <span class="text-gold fw-bold small">Regulatory Notice & Risk Warning</span>
                        </div>
                        <p class="text-secondary small mb-0 lh-base">
                            Forex, CFDs, and digital asset trading involve substantial financial risk. KTS Markets provides educational courses, technical market insights, and bot telemetry strictly for educational and informational purposes. Past performance does not guarantee future results.
                        </p>
                    </div>
                </div>

                <!-- Page Footer -->
                <div class="text-center mt-4 text-secondary small">
                    <p class="mb-1">© 2026 KTS Markets. All rights reserved.</p>
                    <p>Inquiries: <a href="mailto:privacy@ktsmarkets.com" class="text-gold text-decoration-none">privacy@ktsmarkets.com</a> | WhatsApp: +92 337 1244640</p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>