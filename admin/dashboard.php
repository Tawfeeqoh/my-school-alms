<?php
// ============================================================
// ALMS — Admin Dashboard
// ============================================================
$currentPage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$db = db();

// 1. Fetch System Stats
$stats = [];

// Total Students
$stdStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'student'");
$stats['students'] = $stdStmt->fetch()['total'];

// Total Lecturers
$lecStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'lecturer'");
$stats['lecturers'] = $lecStmt->fetch()['total'];

// Pending Approval Lecturers
$pendStmt = $db->query("SELECT COUNT(*) AS total FROM users WHERE role = 'lecturer' AND status = 'pending'");
$stats['pending_lecturers'] = $pendStmt->fetch()['total'];

// Total Courses
$crsStmt = $db->query("SELECT COUNT(*) AS total FROM courses");
$stats['courses'] = $crsStmt->fetch()['total'];

// Total Departments
$deptStmt = $db->query("SELECT COUNT(*) AS total FROM departments");
$stats['departments'] = $deptStmt->fetch()['total'];

// 2. Fetch Recent Activities (Last 20)
$activities = [];
try {
    $actStmt = $db->query("
        SELECT a.action, a.ip_address, a.timestamp, u.first_name, u.last_name, u.role
        FROM activity_log a
        JOIN users u ON a.user_id = u.id
        ORDER BY a.timestamp DESC LIMIT 20
    ");
    $activities = $actStmt->fetchAll();
} catch (PDOException $e) {
    // Activity log structure fallback
}

// 3. Fetch Departments Overview (List all with Student & Lecturer Counts)
$deptsOverview = [];
try {
    $ovStmt = $db->query("
        SELECT d.id, d.name, d.level_offered,
               (SELECT COUNT(*) FROM student_profiles WHERE department_id = d.id) AS student_count,
               (SELECT COUNT(*) FROM lecturer_profiles WHERE primary_department_id = d.id) AS lecturer_count
        FROM departments d
        ORDER BY d.name ASC
    ");
    $deptsOverview = $ovStmt->fetchAll();
} catch (PDOException $e) {
    //
}

$pageTitle = 'Admin Dashboard';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-admin.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Alerts section -->
        <?php if ($stats['pending_lecturers'] > 0): ?>
            <div class="flash-msg warning" style="margin-bottom: var(--sp-6);">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>There are <strong><?= $stats['pending_lecturers'] ?></strong> lecturers awaiting administrative credentials verification. <a href="/admin/lecturers.php" style="text-decoration: underline; margin-left: 8px;">Review approvals &rarr;</a></span>
            </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="stats-row stats-row-5 mb-6">
            <div class="stat-card">
                <div class="stat-card-icon blue">🎓</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $stats['students'] ?></div>
                    <div class="stat-card-label">Students</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon red">🔬</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $stats['lecturers'] ?></div>
                    <div class="stat-card-label">Lecturers</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon amber">⏳</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $stats['pending_lecturers'] ?></div>
                    <div class="stat-card-label">Pending Approval</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon green">📚</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $stats['courses'] ?></div>
                    <div class="stat-card-label">Courses Register</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon blue">🏛️</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $stats['departments'] ?></div>
                    <div class="stat-card-label">Departments</div>
                </div>
            </div>
        </div>

        <!-- 2/3 and 1/3 Content Grid -->
        <div class="content-grid">
            
            <!-- Left Panel: Recent Audit Logs -->
            <div class="dashboard-widget" style="overflow-x: auto;">
                <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: var(--sp-4);">Security Activity Logs</h3>
                <?php if (empty($activities)): ?>
                    <p class="text-muted text-sm">No activity recorded yet.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $act): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($act['first_name'] . ' ' . $act['last_name']) ?></strong></td>
                                    <td><span class="badge badge-neutral"><?= htmlspecialchars(strtoupper($act['role'])) ?></span></td>
                                    <td><span class="font-mono-data"><?= htmlspecialchars($act['action']) ?></span></td>
                                    <td class="font-mono-data text-muted"><?= htmlspecialchars($act['ip_address']) ?></td>
                                    <td class="font-mono-data text-muted" data-date="<?= $act['timestamp'] ?>" data-date-style="datetime"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Right Panel: Departments Summary -->
            <div class="dashboard-widget">
                <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: var(--sp-4);">Departments Overview</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php if (empty($deptsOverview)): ?>
                        <p class="text-muted text-sm">No departments available.</p>
                    <?php else: ?>
                        <?php foreach ($deptsOverview as $dept): ?>
                            <div style="padding: 12px; border-radius: 12px; border: 1px solid var(--clr-border-light); background: var(--clr-bg); display: flex; flex-direction: column; gap: 4px;">
                                <div style="font-weight: 700; font-size: 0.875rem; color: var(--clr-text);"><?= htmlspecialchars($dept['name']) ?></div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--clr-text-secondary);">
                                    <span><?= htmlspecialchars($dept['level_offered']) ?></span>
                                    <span>👨‍🏫 <?= $dept['lecturer_count'] ?> &middot; 🎓 <?= $dept['student_count'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
