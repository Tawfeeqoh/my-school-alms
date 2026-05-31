<?php
// ============================================================
// ALMS — Authentication Handler
// Handles: login, register, logout
// ============================================================

require_once __DIR__ . '/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── LOGOUT ──────────────────────────────────────────────────
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    redirect('/index.php?msg=logged_out');
}

// ── LOGIN ────────────────────────────────────────────────────
if ($action === 'login') {
    requireGuest();
    verifyCsrf();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        redirect('/index.php?error=missing_fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect('/index.php?error=invalid_email');
    }

    $pdo  = db();
    $stmt = $pdo->prepare('SELECT id, email, password_hash, role, first_name, last_name, title, status, login_attempts, locked_until FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Account not found — generic error to prevent enumeration
    if (!$user) {
        redirect('/index.php?error=invalid_credentials');
    }

    // Check account lock
    if ($user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
        redirect('/index.php?error=account_locked');
    }

    // Check status
    if ($user['status'] === 'pending') {
        redirect('/index.php?error=pending_approval');
    }
    if ($user['status'] === 'suspended') {
        redirect('/index.php?error=account_suspended');
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        // Increment attempts
        $attempts = $user['login_attempts'] + 1;
        $lock_sql = 'UPDATE users SET login_attempts = ? WHERE id = ?';
        $lock_val = [$attempts, $user['id']];

        if ($attempts >= 5) {
            $lockUntil = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
            $lock_sql  = 'UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?';
            $lock_val  = [$attempts, $lockUntil, $user['id']];
        }

        $pdo->prepare($lock_sql)->execute($lock_val);
        redirect('/index.php?error=invalid_credentials');
    }

    // Successful login — reset attempts, record login
    $pdo->prepare('UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?')
        ->execute([$user['id']]);

    // Log activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $pdo->prepare('INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, ?, ?)')
        ->execute([$user['id'], 'login', $ip]);

    // Set session
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['role']       = $user['role'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['title']      = $user['title'];

    // Redirect by role
    redirect(match($user['role']) {
        'admin'    => '/admin/dashboard.php',
        'lecturer' => '/lecturer/dashboard.php',
        default    => '/student/dashboard.php',
    });
}

// ── REGISTER ─────────────────────────────────────────────────
if ($action === 'register') {
    requireGuest();
    verifyCsrf();

    $first_name    = trim($_POST['first_name'] ?? '');
    $last_name     = trim($_POST['last_name']  ?? '');
    $email         = trim($_POST['email']      ?? '');
    $password      = $_POST['password']        ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';
    $role          = $_POST['role']            ?? 'student';
    $level_id      = (int)($_POST['level_id']  ?? 0);
    $matric_number = trim($_POST['matric_number'] ?? '');
    $title         = trim($_POST['title']      ?? 'Student');

    // Validate
    $errors = [];

    if (!$first_name || !$last_name) $errors[] = 'name_required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'invalid_email';
    if (strlen($password) < 8) $errors[] = 'password_short';
    if ($password !== $confirm) $errors[] = 'password_mismatch';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'password_weak';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'password_weak';
    if (!in_array($role, ['student', 'lecturer'])) $errors[] = 'invalid_role';
    
    if ($role === 'student') {
        if (!$level_id) $errors[] = 'level_required';
        if (!$matric_number) $errors[] = 'matric_required';
    }

    if ($errors) {
        redirect('/register.php?error=' . implode(',', $errors));
    }

    $pdo = db();

    $dept_id = (int)($_POST['department_id'] ?? 0);
    $deptStmt = $pdo->prepare('SELECT id, name, level_offered FROM departments WHERE id = ? LIMIT 1');
    $deptStmt->execute([$dept_id]);
    $department = $deptStmt->fetch();
    if (!$department) {
        $errors[] = 'department_required';
    } elseif ($role === 'student' && $department['level_offered'] === 'ND ONLY' && $level_id > 2) {
        $errors[] = 'hnd_not_available';
    }

    if ($errors) {
        redirect('/register.php?error=' . implode(',', $errors));
    }

    // Check duplicate email
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        redirect('/register.php?error=email_exists');
    }

    // STUDENT: Verify against pre-authorized matric codes (Outsider Registry Check)
    if ($role === 'student') {
        $matricCheck = $pdo->prepare('SELECT id, is_used FROM authorized_matric_numbers WHERE matric_number = ? LIMIT 1');
        $matricCheck->execute([$matric_number]);
        $authorized = $matricCheck->fetch();

        if (!$authorized) {
            redirect('/register.php?error=unauthorized_matric'); // Not a real school matric code
        }
        if ($authorized['is_used']) {
            redirect('/register.php?error=matric_already_used'); // Code already claimed
        }
    }

    // Status: students are active immediately; lecturers are pending admin approval
    $status = ($role === 'student') ? 'active' : 'pending';
    $userTitle = ($role === 'student') ? 'Student' : $title;

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $pdo->beginTransaction();
    try {
        // Insert User
        $pdo->prepare('INSERT INTO users (email, password_hash, role, first_name, last_name, title, status) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$email, $hash, $role, $first_name, $last_name, $userTitle, $status]);

        $userId = (int)$pdo->lastInsertId();

        // Create Profile
        if ($role === 'student') {
            // Update matric code to used state
            $pdo->prepare('UPDATE authorized_matric_numbers SET is_used = 1 WHERE matric_number = ?')
                ->execute([$matric_number]);

            $pdo->prepare('INSERT INTO student_profiles (user_id, matric_number, level_id, department_id) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $matric_number, $level_id, $dept_id]);
        } elseif ($role === 'lecturer') {
            $pdo->prepare('INSERT INTO lecturer_profiles (user_id, primary_department_id) VALUES (?, ?)')
                ->execute([$userId, $dept_id]);
        }

        // Log activity
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $pdo->prepare('INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, ?, ?)')
            ->execute([$userId, 'register', $ip]);

        $pdo->commit();

        if ($role === 'lecturer') {
            redirect('/index.php?msg=registered');
        } else {
            // Auto-login student
            session_regenerate_id(true);
            $_SESSION['user_id']    = $userId;
            $_SESSION['role']       = $role;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name']  = $last_name;
            $_SESSION['email']      = $email;
            $_SESSION['title']      = $userTitle;
            redirect('/student/onboarding.php');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Registration error: ' . $e->getMessage());
        redirect('/register.php?error=server_error');
    }
}

// Unknown action
redirect('/index.php');
