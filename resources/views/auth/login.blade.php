<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KTS 10 Pips Bots</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 50%, #f5f3ff 100%); min-height: 100vh; }
        .login-card { max-width: 420px; margin: 0 auto; }
        .card { border-radius: 1rem; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        .form-control { border-radius: 0.5rem; padding: 0.65rem 1rem; border-color: #e5e7eb; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
        .btn-primary { background: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; padding: 0.65rem; font-weight: 600; }
        .btn-primary:hover { background: #3730a3; border-color: #3730a3; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="login-card w-100 p-3">
        <div class="card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                        <i class="bi bi-graph-up-arrow text-white fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1" style="color:#111827;">KTS 10 Pips Bots</h4>
                    <p class="text-secondary mb-0">Super Admin Panel</p>
                </div>

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="admin@example.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label text-secondary" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
            </div>
        </div>
        <p class="text-center text-muted mt-3 small">&copy; {{ date('Y') }} Codex Aura Solutions</p>
    </div>
</body>
</html>
