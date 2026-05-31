<?php
// ============================================================
// ALMS — Database Configuration
// InfinityFree / cPanel compatible
// ============================================================

define('DB_HOST', 'sql102.infinityfree.comur InfinityFree MySQL host
define('DB_NAME', 'if0_41958528_fcahptibalms'); // ← your DB name from the schema
define('DB_USER', 'if0_41958528');                // ← your cPanel DB username
define('DB_PASS', 'qH4OLN9mHQ');                            // ← your cPanel DB password

define('SITE_URL', 'https://fcahptibalms.great-site.net'); // ← your domain
define('SITE_NAME', 'ALMS — FCAH&PT Ibadan');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated(): bool {
    return !empty($_SESSION['user_id']);
}

// PDO connection
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            // Don't expose DB errors in production
            error_log('DB Connection failed: ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed. Please try again later.']));
        }
    }
    return $pdo;
}

// Redirect helper
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// Auth guards
function requireGuest(): void {
    if (!empty($_SESSION['user_id'])) {
        $role = $_SESSION['role'] ?? 'student';
        redirect(match($role) {
            'admin'    => '/admin/dashboard.php',
            'lecturer' => '/lecturer/dashboard.php',
            default    => '/student/dashboard.php',
        });
    }
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('/index.php?error=session_expired');
    }
}

// CSRF helpers
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid request token.');
    }
}

// Sanitize output
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
