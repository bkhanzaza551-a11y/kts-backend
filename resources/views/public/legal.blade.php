<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - KTS Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0B0E11; color: #E0E0E0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .card { background-color: #12161A; border: 1px solid #1E2329; border-radius: 16px; }
        .text-gold { color: #FFD700; }
        .badge-warning { background-color: rgba(255, 215, 0, 0.15); color: #FFD700; border: 1px solid rgba(255, 215, 0, 0.3); }
        a { color: #FFD700; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card p-4 p-md-5 shadow-lg">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3 border-secondary">
                        <div>
                            <h2 class="text-gold fw-bold mb-1">{{ $title }}</h2>
                            <p class="text-secondary small mb-0">Last updated: {{ $updated_at->format('F d, Y') }}</p>
                        </div>
                        <span class="badge badge-warning px-3 py-2 rounded-pill">Official Documentation</span>
                    </div>

                    <div class="legal-content lh-lg text-light">
                        {!! $content !!}
                    </div>

                    <hr class="border-secondary my-4">
                    <div class="p-3 rounded bg-dark border border-warning">
                        <h6 class="text-gold fw-bold mb-1">⚠️ Financial Risk Disclosure</h6>
                        <small class="text-secondary">Forex, CFDs, and cryptocurrency trading carry high risk and may not be suitable for all investors. KTS Markets provides automated analysis and signals for informational and educational purposes only.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>