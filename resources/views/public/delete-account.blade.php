<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account & Data | KTS Markets</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome & SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-main: #0B0E14;
            --card-bg: #121722;
            --border-color: #1E2638;
            --gold-primary: #FFB800;
            --gold-gradient: linear-gradient(135deg, #FFB800 0%, #FF8C00 100%);
            --text-muted: #8E9BAE;
            --danger-color: #EF4444;
        }
        body {
            background-color: var(--bg-main);
            color: #F3F4F6;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .main-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            max-width: 580px;
            width: 100%;
        }
        .card-header-badge {
            background: rgba(239, 68, 68, 0.12);
            color: #F87171;
            border: 1px solid rgba(239, 68, 68, 0.25);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 6px 14px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .text-gold {
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-control, .form-select {
            background-color: #0E121B !important;
            border: 1.5px solid var(--border-color) !important;
            color: #FFFFFF !important;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--gold-primary) !important;
            box-shadow: 0 0 0 3px rgba(255, 184, 0, 0.15) !important;
        }
        .form-check-input {
            background-color: #0E121B;
            border-color: var(--border-color);
            border-radius: 6px;
            width: 1.2rem;
            height: 1.2rem;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        .btn-delete {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1rem;
            padding: 14px 20px;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.4);
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            box-shadow: 0 12px 25px -4px rgba(239, 68, 68, 0.6);
            transform: translateY(-1px);
        }
        .policy-box {
            background: rgba(14, 18, 27, 0.7);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 16px;
        }
        .policy-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .policy-item:last-child {
            margin-bottom: 0;
        }
        .policy-item i {
            color: #10B981;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    <div class="main-card p-4 p-md-5">
        <!-- Brand Header -->
        <div class="text-center mb-4">
            <div class="card-header-badge mb-3">
                <i class="fa-solid fa-shield-halved"></i> DATA SAFETY & PRIVACY PORTAL
            </div>
            <h2 class="fw-bold mb-2">Delete Account & Data</h2>
            <p class="text-secondary small mb-0">
                In compliance with Google Play Developer Policies and data protection regulations, you can submit a request to permanently delete your account and all associated personal data.
            </p>
        </div>

        <!-- Information Box -->
        <div class="policy-box mb-4">
            <div class="policy-item">
                <i class="fa-solid fa-circle-check"></i>
                <span>Profile info, email, avatar, and authentication tokens will be immediately purged.</span>
            </div>
            <div class="policy-item">
                <i class="fa-solid fa-circle-check"></i>
                <span>All chat logs, MT5 bot linkages, and saved preferences will be completely removed.</span>
            </div>
            <div class="policy-item">
                <i class="fa-solid fa-circle-check"></i>
                <span>This action is irreversible. Once deleted, your account cannot be recovered.</span>
            </div>
        </div>

        <!-- Deletion Form -->
        <form id="deleteForm" method="POST" action="{{ route('public.delete-account.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-bold text-white mb-2">Registered Email Address</label>
                <input type="email" name="email" id="emailInput" class="form-control" placeholder="Enter your registered account email" required autocomplete="email">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-white mb-2">Reason for Deletion (Optional)</label>
                <select name="reason" class="form-select">
                    <option value="">Select a reason (optional)</option>
                    <option value="no_longer_needed">I no longer need this service</option>
                    <option value="privacy_concerns">Privacy & data security concerns</option>
                    <option value="switching">Switching to another platform</option>
                    <option value="other">Other reason</option>
                </select>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" required id="confirmCheck">
                <label class="form-check-label text-secondary small user-select-none" for="confirmCheck">
                    I understand that this action is permanent and all my data, connected accounts, and history will be permanently deleted from KTS Markets servers.
                </label>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-delete w-100">
                <i class="fa-solid fa-trash-can me-2"></i> Submit Deletion Request
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="small text-secondary mb-0">
                Need help? Contact support at <a href="mailto:ktsfxtraining@gmail.com" class="text-warning text-decoration-none fw-semibold">ktsfxtraining@gmail.com</a>
            </p>
        </div>
    </div>

    <!-- Script to show SweetAlert2 Popup -->
    <script>
        @if(session('status'))
            Swal.fire({
                title: 'Request Submitted Successfully!',
                html: '<p style="color:#9CA3AF; font-size: 0.95rem; margin-top:8px;">Your account and data deletion request has been received by the support team.<br><br>All associated personal records will be permanently deleted per Google Play Data Safety policies.</p>',
                icon: 'success',
                confirmButtonColor: '#FFB800',
                confirmButtonText: 'Understood',
                background: '#121722',
                color: '#FFFFFF',
                customClass: {
                    popup: 'border border-secondary rounded-4 shadow-lg'
                }
            });
        @endif

        @if($errors->any())
            Swal.fire({
                title: 'Submission Error',
                text: '{{ $errors->first() }}',
                icon: 'error',
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Try Again',
                background: '#121722',
                color: '#FFFFFF'
            });
        @endif

        document.getElementById('deleteForm').addEventListener('submit', function() {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing Request...';
        });
    </script>
</body>
</html>