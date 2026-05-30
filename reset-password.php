<?php
require_once __DIR__ . '/config.php';
requireGuest();

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$email = $_GET['email'] ?? $_POST['email'] ?? '';
$error = '';
$success = false;

if (!$token || !$email) {
    redirect('/index.php?error=invalid_reset_request');
}

$pdo = db();

// Verify token in DB
$stmt = $pdo->prepare('SELECT id, reset_expiry FROM users WHERE email = ? AND reset_token = ? LIMIT 1');
$stmt->execute([$email, $token]);
$user = $stmt->fetch();

if (!$user) {
    redirect('/index.php?error=invalid_reset_token');
}

// Check expiration
if (new DateTime() > new DateTime($user['reset_expiry'])) {
    redirect('/index.php?error=reset_token_expired');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 8) {
        $error = 'password_short';
    } elseif ($password !== $confirm) {
        $error = 'password_mismatch';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'password_weak';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        $pdo->beginTransaction();
        try {
            // Update password, clear reset token and reset login attempts
            $pdo->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expiry = NULL, login_attempts = 0, locked_until = NULL WHERE id = ?')
                ->execute([$hash, $user['id']]);
                
            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'server_error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — ALMS FCAHPT Ibadan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-red: #D10000;
            --bg-grey: #F5F5F7;
            --text-dark: #000;
            --border-grey: #D1D1D6;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-grey);
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 440px;
            width: 100%;
            background: white;
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 12px 48px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.03);
            text-align: center;
        }
        .logo-wrap {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background-color: var(--brand-red);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .logo-wrap svg {
            width: 32px;
            height: 32px;
            fill: white;
        }
        h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        p {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4B5563;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid var(--border-grey);
            background-color: var(--bg-grey);
            font-size: 14px;
            outline: none;
            transition: all 0.2s ease;
        }
        input:focus {
            border-color: var(--brand-red);
            box-shadow: 0 0 0 3px rgba(209,0,0,0.1);
        }
        .btn {
            width: 100%;
            padding: 14px;
            border-radius: 9999px;
            border: none;
            background-color: var(--brand-red);
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(209,0,0,0.3);
        }
        .alert {
            padding: 14px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }
        .alert-success {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FEE2E2;
        }
        .back-link {
            display: inline-block;
            margin-top: 24px;
            font-size: 14px;
            color: var(--brand-red);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrap">
            <svg viewBox="0 0 24 24">
                <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                <path d="M5 12.5V17c0 1.66 3.13 3 7 3s7-1.34 7-3v-4.5l-7 3.82-7-3.82z"/>
            </svg>
        </div>
        
        <h2>Set New Password</h2>
        <p>Your identity has been verified. Choose a strong password containing at least 8 characters, a number, and an uppercase letter.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Password updated successfully! You can now log in using your new credentials.
            </div>
            <a href="index.php" class="btn" style="text-decoration: none; display: block; text-align: center;">Go to Log In</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php
                    echo match($error) {
                        'password_short' => 'Password must be at least 8 characters long.',
                        'password_mismatch' => 'Passwords do not match. Please try again.',
                        'password_weak' => 'Password is too weak. Ensure it includes an uppercase letter and a number.',
                        default => 'An unexpected server error occurred. Please try again.'
                    };
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <input type="hidden" name="email" value="<?php echo e($email); ?>">
                
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn">Update Password</button>
            </form>
            
            <a href="index.php" class="back-link">Cancel</a>
        <?php endif; ?>
    </div>
</body>
</html>
