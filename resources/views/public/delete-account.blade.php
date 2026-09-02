<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account & Data Deletion - KTS 10 Pips Bots</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0B0E11; color: #E0E0E0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .card { background-color: #12161A; border: 1px solid #1E2329; border-radius: 16px; }
        .text-gold { color: #FFD700; }
        .btn-danger-custom { background-color: #FF4444; border: none; font-weight: 700; }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card p-4 p-md-5 shadow-lg">
                    <h3 class="text-gold fw-bold mb-2 text-center">Delete Account & Data</h3>
                    <p class="text-secondary text-center small mb-4">Per Google Play & Apple Data Safety guidelines, you can request permanent deletion of your account and all associated personal data.</p>

                    @if(session('status'))
                        <div class="alert alert-success bg-dark border-success text-success mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.delete-account.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-bold">Registered Email Address</label>
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required placeholder="your.email@example.com">
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" required id="confirmCheck">
                            <label class="form-check-label text-secondary small" for="confirmCheck">
                                I understand that this action is permanent and all my data, connected accounts, and history will be permanently deleted.
                            </label>
                        </div>
                        <button type="submit" class="btn btn-danger btn-danger-custom w-100 py-3">Request Permanent Deletion</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>