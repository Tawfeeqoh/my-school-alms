<?php
// ============================================================
// ALMS — Private Administrator Portal Login
// ============================================================
require_once __DIR__ . '/../config.php';

if (isAuthenticated()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        header('Location: /index.php');
        exit;
    }
}

$error = $_GET['error'] ?? '';
$error_msg = '';
if ($error === 'invalid_credentials') {
    $error_msg = 'Invalid administrative credentials.';
} elseif ($error === 'account_locked') {
    $error_msg = 'Account temporarily locked due to failed attempts.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALMS System Administration — Login</title>
    <link rel="stylesheet" href="/assets/css/index.css">
    <style>
        body.admin-body {
            background-color: var(--clr-dark-bg);
            color: var(--clr-dark-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .admin-glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-2xl);
            padding: var(--sp-8);
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
        }
        .admin-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--clr-dark-text) !important;
        }
        .admin-input:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--clr-primary) !important;
            box-shadow: 0 0 0 4px rgba(209, 0, 0, 0.3) !important;
        }
    </style>
</head>
<body class="admin-body film-grain">

    <div class="admin-glass-card animate-fade-in-up">
        
        <div style="text-align: center; margin-bottom: var(--sp-8);">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: var(--clr-primary); border-radius: 12px; color: #FFFFFF; font-weight: 800; font-size: 1.5rem; margin-bottom: var(--sp-4);">
                AD
            </div>
            <h1 class="hero-text-cinematic" style="font-size: 1.5rem; color: #FFFFFF; letter-spacing: -0.01em;">System Administration</h1>
            <p style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--clr-dark-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 8px;">Authorized Personnel Only</p>
        </div>

        <?php if ($error_msg): ?>
            <div class="flash-msg error" style="background: rgba(255,59,48,0.15); border-color: rgba(255,59,48,0.3); color: #FF453A;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Admin Login Form -->
        <form action="/auth.php?action=login" method="POST" style="display: flex; flex-direction: column; gap: var(--sp-5);">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            
            <div class="input-group">
                <label for="admin-email" style="color: #E5E5EA;">Admin Email</label>
                <input type="email" id="admin-email" name="email" required placeholder="admin@fcahptib.edu.ng" class="input-field admin-input">
            </div>

            <div class="input-group">
                <label for="admin-password" style="color: #E5E5EA;">Password</label>
                <div class="input-password-wrap">
                    <input type="password" id="admin-password" name="password" required class="input-field admin-input">
                    <button type="button" class="toggle-pw" aria-label="Toggle Password Visibility" style="color: rgba(255,255,255,0.4);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full animate-pulse-glow" style="margin-top: 10px;">Authenticate System</button>
        </form>

        <div style="text-align: center; margin-top: var(--sp-6);">
            <a href="/" style="font-size: 0.8125rem; color: var(--clr-dark-muted); text-decoration: underline;">Return to Public Portal</a>
        </div>

    </div>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
