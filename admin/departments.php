<?php
// ============================================================
// ALMS — Admin Department Management Desk
// ============================================================
$currentPage = 'departments';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$db = db();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// ── 1. POST ACTION HANDLER ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $level = trim($_POST['level_offered'] ?? 'ND & HND');

        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO departments (name, level_offered) VALUES (?, ?)");
                $stmt->execute([$name, $level]);
                redirect('/admin/departments.php?msg=created');
            } catch (PDOException $e) {
                redirect('/admin/departments.php?error=duplicate_dept');
            }
        } else {
            redirect('/admin/departments.php?error=missing_name');
        }
    }
}

// ── 2. QUERY REGISTERED DEPARTMENTS ────────────────────────
$departments = $db->query("
    SELECT d.id, d.name, d.level_offered,
           (SELECT COUNT(*) FROM student_profiles WHERE department_id = d.id) AS student_count,
           (SELECT COUNT(*) FROM lecturer_profiles WHERE primary_department_id = d.id) AS lecturer_count,
           (SELECT COUNT(*) FROM courses WHERE department_id = d.id) AS course_count
    FROM departments d
    ORDER BY d.name ASC
")->fetchAll();

$pageTitle = 'Academic Departments';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-admin.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Status Messages -->
        <?php if ($msg === 'created'): ?>
            <div class="flash-msg success">Academic department created successfully.</div>
        <?php endif; ?>

        <?php if ($error === 'duplicate_dept'): ?>
            <div class="flash-msg error">A department with this name already exists.</div>
        <?php elseif ($error === 'missing_name'): ?>
            <div class="flash-msg error">Please enter a valid department name.</div>
        <?php endif; ?>

        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-bottom: var(--sp-6); gap:16px;">
            <div>
                <h2 style="font-size: 1.5rem; font-weight:700;">Academic Departments</h2>
                <p class="text-secondary text-sm">Create and inspect institutional departments offering ND/HND programs.</p>
            </div>
            <button class="btn btn-primary" onclick="openCreateModal()">Create Department</button>
        </div>

        <!-- Department List Table -->
        <div class="dashboard-widget" style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Program Offered</th>
                        <th>teachable courses</th>
                        <th>Active Staff</th>
                        <th>Registered Students</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($d['level_offered']) ?></span></td>
                            <td class="font-mono-data text-muted"><?= $d['course_count'] ?> courses</td>
                            <td class="font-mono-data text-muted"><?= $d['lecturer_count'] ?> staff</td>
                            <td class="font-mono-data text-muted"><?= $d['student_count'] ?> students</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- ── CREATE DEPARTMENT MODAL ── -->
<div class="modal-overlay" id="create-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:1.125rem; font-weight:700; margin:0;">Create Academic Department</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form action="/admin/departments.php" method="POST" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="create">

            <div class="input-group">
                <label for="dept-name">Department Name</label>
                <input type="text" id="dept-name" name="name" required placeholder="e.g. Agricultural Extension & Management" class="input-field">
            </div>

            <div class="input-group">
                <label for="dept-level">Program Mode</label>
                <select name="level_offered" id="dept-level" required class="input-field">
                    <option value="ND & HND">National Diploma (ND) & Higher National Diploma (HND)</option>
                    <option value="ND ONLY">National Diploma (ND) Only</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">Confirm & Create</button>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.add('active');
}
function closeCreateModal() {
    document.getElementById('create-modal').classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
