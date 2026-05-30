<?php
require_once __DIR__ . '/config.php';
requireGuest();

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'invalid_email';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expiry = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
            
            // Alter users table dynamically to support reset if columns are missing
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL");
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expiry DATETIME DEFAULT NULL");
            } catch (Exception $e) {}
            
            $pdo->prepare('UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?')
                ->execute([$token, $expiry, $user['id']]);
                
            // Send email via mailer helper
            require_once __DIR__ . '/mailer.php';
            $resetLink = SITE_URL . '/reset-password.php?token=' . $token . '&email=' . urlencode($email);
            $subject = "ALMS Password Reset Code";
            $body = "Hi,\n\nWe received a request to reset your password for " . SITE_NAME . ".\nClick the link below to set a new password:\n\n" . $resetLink . "\n\nThis link will expire in 1 hour.\nIf you did not make this request, please ignore this email.";
            
            if (sendMail($email, $subject, $body)) {
                $success = true;
            } else {
                $error = 'mail_delivery_failed';
            }
        } else {
            // For security, do not disclose if email is registered
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — ALMS FCAHPT Ibadan</title>
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
        
        <h2>Recover Password</h2>
        <p>Enter your institutional email address below and we'll dispatch a link to securely reset your credentials.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                If the email matches an active profile, a secure recovery code has been dispatched to your inbox. Please check spam if missing.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php
                echo match($error) {
                    'invalid_email' => 'Please provide a valid institutional email address.',
                    'mail_delivery_failed' => 'Mail delivery failed. Please check mailer settings or contact support.',
                    default => 'An unexpected server error occurred.'
                };
                ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                <div class="input-group">
                    <label for="email">Institutional Email</label>
                    <input type="email" id="email" name="email" required placeholder="your.name@student.fcahptib.edu.ng">
                </div>
                <button type="submit" class="btn">Send Recovery Link</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="back-link">Return to Log In</a>
    </div>
</body>
</html>
