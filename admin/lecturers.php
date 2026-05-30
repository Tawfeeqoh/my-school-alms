<?php
// ============================================================
// ALMS — Admin Lecturer Management Desk
// ============================================================
$currentPage = 'lecturers';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$db = db();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// ── 1. POST ACTION HANDLERS ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $lecId = (int)($_POST['lecturer_id'] ?? 0);
        if ($lecId > 0) {
            $db->beginTransaction();
            try {
                $stmt1 = $db->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'lecturer'");
                $stmt1->execute([$lecId]);
                
                $stmt2 = $db->prepare("UPDATE lecturer_profiles SET approved_at = NOW() WHERE user_id = ?");
                $stmt2->execute([$lecId]);

                // Create alert notification for lecturer
                $notif = $db->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Account Approved', 'Your lecturer credentials have been verified by the administrator. Welcome aboard!')");
                $notif->execute([$lecId]);

                $db->commit();
                redirect('/admin/lecturers.php?msg=approved');
            } catch (Exception $e) {
                $db->rollBack();
                redirect('/admin/lecturers.php?error=db_error');
            }
        }
    }

    if ($action === 'suspend') {
        $lecId = (int)($_POST['lecturer_id'] ?? 0);
        if ($lecId > 0) {
            $stmt = $db->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role = 'lecturer'");
            $stmt->execute([$lecId]);
            redirect('/admin/lecturers.php?msg=suspended');
        }
    }

    if ($action === 'unsuspend') {
        $lecId = (int)($_POST['lecturer_id'] ?? 0);
        if ($lecId > 0) {
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'lecturer'");
            $stmt->execute([$lecId]);
            redirect('/admin/lecturers.php?msg=unsuspended');
        }
    }

    if ($action === 'assign_course') {
        $lecId = (int)($_POST['lecturer_id'] ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);
        $deptId = (int)($_POST['department_id'] ?? 0);

        if ($lecId > 0 && $courseId > 0 && $deptId > 0) {
            try {
                $stmt = $db->prepare("INSERT INTO lecturer_course_assignments (lecturer_id, course_id, department_id) VALUES (?, ?, ?)");
                $stmt->execute([$lecId, $courseId, $deptId]);
                redirect('/admin/lecturers.php?msg=course_assigned');
            } catch (PDOException $e) {
                redirect('/admin/lecturers.php?error=duplicate_assignment');
            }
        }
    }

    if ($action === 'remove_course') {
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        if ($assignmentId > 0) {
            $stmt = $db->prepare("DELETE FROM lecturer_course_assignments WHERE id = ?");
            $stmt->execute([$assignmentId]);
            redirect('/admin/lecturers.php?msg=course_removed');
        }
    }
}

// ── 2. QUERY REGISTERED STAFF ──────────────────────────────
// Fetch Pending Approval staff
$pendingStaff = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.email, u.title, u.created_at, d.name AS dept_name
    FROM users u
    JOIN lecturer_profiles lp ON u.id = lp.user_id
    JOIN departments d ON lp.primary_department_id = d.id
    WHERE u.role = 'lecturer' AND u.status = 'pending'
    ORDER BY u.created_at ASC
")->fetchAll();

// Fetch Active & Suspended staff
$activeStaff = $db->query("
    SELECT u.id, u.first_name, u.last_name, u.email, u.title, u.status, lp.approved_at, d.name AS dept_name
    FROM users u
    JOIN lecturer_profiles lp ON u.id = lp.user_id
    JOIN departments d ON lp.primary_department_id = d.id
    WHERE u.role = 'lecturer' AND u.status IN ('active', 'suspended')
    ORDER BY u.first_name ASC, u.last_name ASC
")->fetchAll();

// Fetch all courses for modal assignment dropdown
$allCourses = $db->query("SELECT id, course_code, course_name FROM courses ORDER BY course_code ASC")->fetchAll();
$allDepts = $db->query("SELECT id, name FROM departments ORDER BY name ASC")->fetchAll();

$pageTitle = 'Manage Staff';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-admin.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Status Messages -->
        <?php if ($msg === 'approved'): ?>
            <div class="flash-msg success">Lecturer credentials verified and account activated.</div>
        <?php elseif ($msg === 'suspended'): ?>
            <div class="flash-msg warning">Lecturer account suspended successfully.</div>
        <?php elseif ($msg === 'unsuspended'): ?>
            <div class="flash-msg success">Lecturer account status restored to active.</div>
        <?php elseif ($msg === 'course_assigned'): ?>
            <div class="flash-msg success">Course successfully assigned to lecturer portfolio.</div>
        <?php elseif ($msg === 'course_removed'): ?>
            <div class="flash-msg info">Course assignment deleted from staff portfolio.</div>
        <?php endif; ?>

        <?php if ($error === 'duplicate_assignment'): ?>
            <div class="flash-msg error">This course and department combination is already assigned to this lecturer.</div>
        <?php elseif ($error === 'db_error'): ?>
            <div class="flash-msg error">Database error processing approval request.</div>
        <?php endif; ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: var(--sp-6);">
            <div>
                <h2 style="font-size: 1.5rem; font-weight:700;">Academic Staff Management</h2>
                <p class="text-secondary text-sm">Approve lecturer logins, assign teachable courses, and audit classroom assignments.</p>
            </div>
        </div>

        <!-- Tabs Switcher -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('pending', this)">
                Pending Approval (<?= count($pendingStaff) ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('active', this)">
                Active Staff (<?= count($activeStaff) ?>)
            </button>
        </div>

        <!-- ── TAB 1: PENDING APPROVAL ── -->
        <div id="tab-pending" class="dashboard-widget tab-content active" style="overflow-x: auto;">
            <?php if (empty($pendingStaff)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">✅</div>
                    <div class="empty-state-title">No Pending Requests</div>
                    <p class="empty-state-desc">All lecturer login credentials are verified and active.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Academic Email</th>
                            <th>Primary Department</th>
                            <th>Registered Date</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingStaff as $lec): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($lec['title'] . ' ' . $lec['first_name'] . ' ' . $lec['last_name']) ?></strong></td>
                                <td class="font-mono-data text-muted"><?= htmlspecialchars($lec['email']) ?></td>
                                <td><?= htmlspecialchars($lec['dept_name']) ?></td>
                                <td class="font-mono-data text-muted" data-date="<?= $lec['created_at'] ?>"></td>
                                <td style="text-align:right; display:flex; justify-content:flex-end; gap:8px;">
                                    <form action="/admin/lecturers.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="lecturer_id" value="<?= $lec['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Verify & Approve</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ── TAB 2: ACTIVE & SUSPENDED STAFF ── -->
        <div id="tab-active" class="dashboard-widget tab-content" style="display:none; overflow-x: auto;">
            <?php if (empty($activeStaff)): ?>
                <p class="text-muted text-center py-6">No active staff members registered.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title & Name</th>
                            <th>Email</th>
                            <th>Primary Dept</th>
                            <th>Teachable Assignments</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeStaff as $lec): 
                            // Fetch course assignments for this lecturer
                            $coursesAssigned = $db->prepare("
                                SELECT lca.id AS assignment_id, c.course_code, d.name AS dept_name
                                FROM lecturer_course_assignments lca
                                JOIN courses c ON lca.course_id = c.id
                                JOIN departments d ON lca.department_id = d.id
                                WHERE lca.lecturer_id = ?
                            ");
                            $coursesAssigned->execute([$lec['id']]);
                            $asList = $coursesAssigned->fetchAll();
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($lec['title'] . ' ' . $lec['first_name'] . ' ' . $lec['last_name']) ?></strong>
                                    <?php if ($lec['status'] === 'suspended'): ?>
                                        <span class="badge badge-red" style="margin-left:4px;">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-mono-data text-muted"><?= htmlspecialchars($lec['email']) ?></td>
                                <td><?= htmlspecialchars($lec['dept_name']) ?></td>
                                <td>
                                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                        <?php if (empty($asList)): ?>
                                            <span class="text-xs text-muted">None assigned</span>
                                        <?php else: ?>
                                            <?php foreach ($asList as $as): ?>
                                                <span class="badge badge-blue" style="display:inline-flex; align-items:center; gap:4px;">
                                                    <?= htmlspecialchars($as['course_code']) ?> (<?= substr(htmlspecialchars($as['dept_name']), 0, 5) ?>...)
                                                    <form action="/admin/lecturers.php" method="POST" style="display:inline; margin:0;" onsubmit="return confirm('Remove course assignment?')">
                                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                        <input type="hidden" name="action" value="remove_course">
                                                        <input type="hidden" name="assignment_id" value="<?= $as['assignment_id'] ?>">
                                                        <button type="submit" style="background:none; border:none; color:var(--clr-danger); cursor:pointer; font-weight:700; padding:0 2px;">&times;</button>
                                                    </form>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                                        <button class="btn btn-outline btn-sm" onclick="openAssignModal(<?= $lec['id'] ?>, '<?= htmlspecialchars($lec['title'] . ' ' . $lec['first_name'] . ' ' . $lec['last_name']) ?>')">Assign Course</button>
                                        
                                        <form action="/admin/lecturers.php" method="POST" style="margin:0;">
                                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                            <input type="hidden" name="lecturer_id" value="<?= $lec['id'] ?>">
                                            <?php if ($lec['status'] === 'suspended'): ?>
                                                <input type="hidden" name="action" value="unsuspend">
                                                <button type="submit" class="btn btn-success btn-sm">Unsuspend</button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="btn btn-danger btn-sm">Suspend</button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ── COURSE ASSIGNMENT MODAL ── -->
<div class="modal-overlay" id="assign-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 style="font-size:1.125rem; font-weight:700; margin:0;" id="modal-title">Assign Course Portfolio</h3>
            <button class="modal-close" onclick="closeAssignModal()">&times;</button>
        </div>
        <form action="/admin/lecturers.php" method="POST" style="display:flex; flex-direction:column; gap:16px;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="action" value="assign_course">
            <input type="hidden" name="lecturer_id" id="modal-lecturer-id">

            <div class="input-group">
                <label>Staff Member</label>
                <input type="text" id="modal-lecturer-name" class="input-field" readonly style="background:var(--clr-bg); font-weight:600;">
            </div>

            <div class="input-group">
                <label for="modal-course-select">Select Course Code</label>
                <select name="course_id" id="modal-course-select" required class="input-field">
                    <option value="">Choose Course</option>
                    <?php foreach ($allCourses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group">
                <label for="modal-dept-select">Select Teaching Department</label>
                <select name="department_id" id="modal-dept-select" required class="input-field">
                    <option value="">Choose Department</option>
                    <?php foreach ($allDepts as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-hint">Teachable courses can map across different departments.</span>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">Confirm Assignment</button>
        </form>
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-' + tab).style.display = 'block';
}

function openAssignModal(id, name) {
    document.getElementById('modal-lecturer-id').value = id;
    document.getElementById('modal-lecturer-name').value = name;
    document.getElementById('assign-modal').classList.add('active');
}

function closeAssignModal() {
    document.getElementById('assign-modal').classList.remove('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
