<?php
// ============================================================
// ALMS - Database Configuration
// InfinityFree / cPanel compatible
// ============================================================

define('DB_HOST', getenv('ALMS_DB_HOST') ?: 'sql102.infinityfree.com');
define('DB_NAME', getenv('ALMS_DB_NAME') ?: 'if0_41958528_fcahptibalms');
define('DB_USER', getenv('ALMS_DB_USER') ?: 'if0_41958528');
define('DB_PASS', getenv('ALMS_DB_PASS') ?: 'qH4OLN9mHQ');

define('SITE_URL', getenv('ALMS_SITE_URL') ?: 'https://fcahptibalms.great-site.net');
define('FRONTEND_URL', getenv('ALMS_FRONTEND_URL') ?: SITE_URL);
define('SITE_NAME', 'ALMS - FCAH&PT Ibadan');

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

function canonicalDepartments(): array {
    return [
        ['id' => 1, 'name' => 'Computer Science', 'level_offered' => 'ND & HND'],
        ['id' => 2, 'name' => 'Science Laboratory Technology', 'level_offered' => 'ND & HND'],
        ['id' => 3, 'name' => 'Animal Health', 'level_offered' => 'ND & HND'],
        ['id' => 4, 'name' => 'Animal Production', 'level_offered' => 'ND & HND'],
        ['id' => 5, 'name' => 'Statistics', 'level_offered' => 'ND & HND'],
        ['id' => 6, 'name' => 'Veterinary', 'level_offered' => 'ND & HND'],
        ['id' => 7, 'name' => 'Biology', 'level_offered' => 'ND & HND'],
        ['id' => 8, 'name' => 'Microbiology', 'level_offered' => 'ND & HND'],
        ['id' => 9, 'name' => 'Physics', 'level_offered' => 'ND & HND'],
        ['id' => 10, 'name' => 'Agricultural Extension', 'level_offered' => 'ND ONLY'],
        ['id' => 11, 'name' => 'Fishery', 'level_offered' => 'ND ONLY'],
    ];
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
            error_log('DB Connection failed: ' . $e->getMessage());
            if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                apiJson(['success' => false, 'message' => 'Database connection failed. Please try again later.'], 503);
            }
            die('Database connection failed. Please try again later.');
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

function requireRole(string $role): void {
    requireAuth();
    if (($_SESSION['role'] ?? '') !== $role) {
        redirect('/index.php?error=forbidden');
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

function verifyCsrfFromRequest(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        apiJson(['success' => false, 'message' => 'Invalid request token.'], 403);
    }
}

function apiCors(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = array_filter(array_unique([SITE_URL, FRONTEND_URL]));

    $isVercelPreview = (bool)preg_match('/^https:\/\/[a-z0-9-]+\.vercel\.app$/i', $origin);
    if ($origin && (in_array($origin, $allowedOrigins, true) || $isVercelPreview)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function apiJson(array $payload, int $status = 200): void {
    apiCors();
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function readJsonInput(): array {
    $input = json_decode(file_get_contents('php://input'), true);
    return is_array($input) ? $input : [];
}

// Sanitize output
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
