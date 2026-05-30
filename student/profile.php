<?php
// ============================================================
// ALMS — Student Profile Settings
// ============================================================
$currentPage = 'profile';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$db = db();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// ── 1. POST ACTION HANDLER ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $vark = strtolower(trim($_POST['vark_style'] ?? 'r'));
    $pace = strtolower(trim($_POST['current_pace'] ?? 'standard'));

    if (!empty($first_name) && !empty($last_name)) {
        $db->beginTransaction();
        try {
            // Update User details
            $uStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
            $uStmt->execute([$first_name, $last_name, $_SESSION['user_id']]);

            // Update Student Profile
            $pStmt = $db->prepare("UPDATE student_profiles SET vark_style = ?, current_pace = ? WHERE user_id = ?");
            $pStmt->execute([$vark, $pace, $_SESSION['user_id']]);

            // Update session cache
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['vark_style'] = $vark;
            $_SESSION['current_pace'] = $pace;

            $db->commit();
            redirect('/student/profile.php?msg=updated');
        } catch (Exception $e) {
            $db->rollBack();
            redirect('/student/profile.php?error=db_error');
        }
    } else {
        redirect('/student/profile.php?error=missing_fields');
    }
}

// ── 2. FETCH DETAILS ───────────────────────────────────────
$stmt = $db->prepare("
    SELECT u.first_name, u.last_name, u.email, s.matric_number, s.level_id, s.who5_score, s.vark_style, s.current_pace, d.name AS dept_name
    FROM users u
    JOIN student_profiles s ON u.id = s.user_id
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$level_name = ($user['level_id'] <= 2) ? 'ND' : 'HND';
$level_full = match((int)$user['level_id']) {
    1 => 'ND I',
    2 => 'ND II',
    3 => 'HND I',
    4 => 'HND II',
    default => 'Roster'
};

$pageTitle = 'My Profile Settings';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in" style="max-width: 640px;">
        
        <!-- Status alerts -->
        <?php if ($msg === 'updated'): ?>
            <div class="flash-msg success">Profile configurations updated successfully.</div>
        <?php endif; ?>

        <?php if ($error === 'missing_fields'): ?>
            <div class="flash-msg error">Please fill in all required text fields.</div>
        <?php elseif ($error === 'db_error'): ?>
            <div class="flash-msg error">Database error updating profile details.</div>
        <?php endif; ?>

        <div style="margin-bottom: var(--sp-6);">
            <h2 style="font-size: 1.5rem; font-weight:700;">My Profile Configurations</h2>
            <p class="text-secondary text-sm">Update personal details, choose VARK study methods, and configure lesson player speed.</p>
        </div>

        <div class="dashboard-widget">
            <form action="/student/profile.php" method="POST" style="display:flex; flex-direction:column; gap:20px;">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="input-group">
                        <label for="first-name">First Name</label>
                        <input type="text" id="first-name" name="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>" class="input-field">
                    </div>
                    
                    <div class="input-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>" class="input-field">
                    </div>
                </div>

                <div class="input-group">
                    <label>Academic Email</label>
                    <input type="email" readonly value="<?= htmlspecialchars($user['email']) ?>" class="input-field" style="background:var(--clr-bg); color:var(--clr-text-secondary); cursor:not-allowed;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="input-group">
                        <label>Matriculation Number</label>
                        <input type="text" readonly value="<?= htmlspecialchars($user['matric_number']) ?>" class="input-field" style="background:var(--clr-bg); color:var(--clr-text-secondary); cursor:not-allowed;">
                    </div>
                    
                    <div class="input-group">
                        <label>Current Study Level</label>
                        <input type="text" readonly value="<?= htmlspecialchars($level_full) ?>" class="input-field" style="background:var(--clr-bg); color:var(--clr-text-secondary); cursor:not-allowed;">
                    </div>
                </div>

                <div class="input-group">
                    <label>Assigned Department</label>
                    <input type="text" readonly value="<?= htmlspecialchars($user['dept_name'] ?? 'General Studies') ?>" class="input-field" style="background:var(--clr-bg); color:var(--clr-text-secondary); cursor:not-allowed;">
                </div>

                <div class="input-group">
                    <label for="pref-vark">Dominant VARK Study Preference</label>
                    <select name="vark_style" id="pref-vark" required class="input-field">
                        <option value="v" <?= $user['vark_style'] === 'v' ? 'selected' : '' ?>>Visual (charts, flow diagrams, visuals)</option>
                        <option value="a" <?= $user['vark_style'] === 'a' ? 'selected' : '' ?>>Auditory (verbal analogies, explanations)</option>
                        <option value="r" <?= $user['vark_style'] === 'r' ? 'selected' : '' ?>>Reading/Writing (lists, outlines, notes)</option>
                        <option value="k" <?= $user['vark_style'] === 'k' ? 'selected' : '' ?>>Kinesthetic (practical case examples, experiments)</option>
                    </select>
                    <span class="input-hint">RAG study assistants use this select option to structure summaries.</span>
                </div>

                <div class="input-group">
                    <label for="pref-pace">Curriculum Learning Pace</label>
                    <select name="current_pace" id="pref-pace" required class="input-field">
                        <option value="express" <?= $user['current_pace'] === 'express' ? 'selected' : '' ?>>⚡ Express Mode (quick key outlines)</option>
                        <option value="standard" <?= $user['current_pace'] === 'standard' ? 'selected' : '' ?>>📖 Standard Mode (balanced lectures)</option>
                        <option value="deep" <?= $user['current_pace'] === 'deep' ? 'selected' : '' ?>>🔬 Deep-Dive Mode (full proofs & notes)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">Save Profile Configuration</button>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
