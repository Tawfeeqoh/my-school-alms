<?php
// ============================================================
// ALMS — Lecturer Course Builder (Lessons & RAG Uploads)
// ============================================================
$currentPage = 'course-builder';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'lecturer') {
    header('Location: /index.php');
    exit;
}

$db = db();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// Fetch all courses assigned to this lecturer
$assignedCourses = $db->prepare("
    SELECT c.id, c.course_code, c.course_name
    FROM lecturer_course_assignments lca
    JOIN courses c ON lca.course_id = c.id
    WHERE lca.lecturer_id = ?
    ORDER BY c.course_code ASC
");
$assignedCourses->execute([$_SESSION['user_id']]);
$courses = $assignedCourses->fetchAll();

// Select active course
$course_id = (int)($_GET['course_id'] ?? ($_POST['course_id'] ?? 0));
if ($course_id === 0 && !empty($courses)) {
    $course_id = (int)$courses[0]['id'];
}

// Check if course belongs to lecturer's assigned list
$ownsCourse = false;
foreach ($courses as $c) {
    if ((int)$c['id'] === $course_id) {
        $ownsCourse = true;
        break;
    }
}

// ── 1. POST ACTION HANDLERS ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ownsCourse) {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_material') {
        $title = trim($_POST['title'] ?? '');
        $text = trim($_POST['content_text'] ?? '');
        
        // Handle file uploads (basic stub configuration)
        $file_path = '/uploads/materials/generic.txt';
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['material_file']['name']);
            $file_path = '/uploads/materials/' . time() . '_' . $fileName;
            move_uploaded_file($_FILES['material_file']['tmp_name'], __DIR__ . '/..' . $file_path);
        }

        if (!empty($title) && (!empty($text) || $file_path !== '/uploads/materials/generic.txt')) {
            $stmt = $db->prepare("INSERT INTO course_materials (course_id, title, file_path, content_text) VALUES (?, ?, ?, ?)");
            $stmt->execute([$course_id, $title, $file_path, $text]);
            redirect("/lecturer/course-builder.php?course_id=$course_id&msg=uploaded");
        } else {
            redirect("/lecturer/course-builder.php?course_id=$course_id&error=missing_fields");
        }
    }

    if ($action === 'add_lesson') {
        $title = trim($_POST['title'] ?? '');
        $std = trim($_POST['content_standard'] ?? '');
        $exp = trim($_POST['content_express'] ?? '');
        $deep = trim($_POST['content_deep'] ?? '');
        $seq = (int)($_POST['sequence_order'] ?? 1);

        if (!empty($title) && !empty($std) && !empty($exp) && !empty($deep)) {
            $stmt = $db->prepare("
                INSERT INTO lessons (course_id, title, content_standard, content_express, content_deep, sequence_order) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$course_id, $title, $std, $exp, $deep, $seq]);
            redirect("/lecturer/course-builder.php?course_id=$course_id&msg=lesson_added");
        } else {
            redirect("/lecturer/course-builder.php?course_id=$course_id&error=missing_fields");
        }
    }

    if ($action === 'delete_material') {
        $matId = (int)($_POST['material_id'] ?? 0);
        if ($matId > 0) {
            $stmt = $db->prepare("DELETE FROM course_materials WHERE id = ? AND course_id = ?");
            $stmt->execute([$matId, $course_id]);
            redirect("/lecturer/course-builder.php?course_id=$course_id&msg=deleted");
        }
    }

    if ($action === 'delete_lesson') {
        $lesId = (int)($_POST['lesson_id'] ?? 0);
        if ($lesId > 0) {
            $stmt = $db->prepare("DELETE FROM lessons WHERE id = ? AND course_id = ?");
            $stmt->execute([$lesId, $course_id]);
            redirect("/lecturer/course-builder.php?course_id=$course_id&msg=lesson_deleted");
        }
    }
}

// ── 2. QUERY COURSE MATERIALS & LESSONS ────────────────────
$materials = [];
$lessons = [];
if ($ownsCourse) {
    $matStmt = $db->prepare("SELECT id, title, file_path, uploaded_at FROM course_materials WHERE course_id = ? ORDER BY uploaded_at DESC");
    $matStmt->execute([$course_id]);
    $materials = $matStmt->fetchAll();

    $lesStmt = $db->prepare("SELECT id, title, sequence_order FROM lessons WHERE course_id = ? ORDER BY sequence_order ASC");
    $lesStmt->execute([$course_id]);
    $lessons = $lesStmt->fetchAll();
}

$pageTitle = 'Course Builder';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-lecturer.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Status Messages -->
        <?php if ($msg === 'uploaded'): ?>
            <div class="flash-msg success">RAG material uploaded and keyword context indexed.</div>
        <?php elseif ($msg === 'lesson_added'): ?>
            <div class="flash-msg success">Lesson added to course modules successfully.</div>
        <?php elseif ($msg === 'deleted'): ?>
            <div class="flash-msg info">Material deleted from RAG memory bank.</div>
        <?php elseif ($msg === 'lesson_deleted'): ?>
            <div class="flash-msg info">Lesson module deleted from syllabus.</div>
        <?php endif; ?>

        <?php if ($error === 'missing_fields'): ?>
            <div class="flash-msg error">Please fill in all required form fields.</div>
        <?php endif; ?>

        <!-- Course Selector and Details -->
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; margin-bottom: var(--sp-6); gap:16px;">
            <div>
                <h2 style="font-size:1.5rem; font-weight:700;">Teachable Course Builder</h2>
                <p class="text-secondary text-sm">Upload context documents for RAG AI engines and structure adaptive syllabus lessons.</p>
            </div>
            
            <div>
                <form action="/lecturer/course-builder.php" method="GET" style="margin:0;">
                    <select name="course_id" onchange="this.form.submit()" class="input-field" style="width:240px; font-weight:600;">
                        <?php if (empty($courses)): ?>
                            <option value="">No Assigned Courses</option>
                        <?php else: ?>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $course_id === (int)$c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['course_code'] . ' — ' . $c['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>
        </div>

        <?php if (!$ownsCourse): ?>
            <div class="card-flat text-center py-8">
                <div style="font-size:3rem; margin-bottom:12px;">🔒</div>
                <h3>Portfolio Locked</h3>
                <p class="text-secondary">Please select an assigned course or request verification credentials from administrator.</p>
            </div>
        <?php else: ?>
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('materials', this)">
                    RAG Course Materials (<?= count($materials) ?>)
                </button>
                <button class="tab-btn" onclick="switchTab('lessons', this)">
                    Syllabus Lessons (<?= count($lessons) ?>)
                </button>
            </div>

            <!-- ── TAB 1: RAG MATERIALS ── -->
            <div id="tab-materials" class="tab-content active" style="display:grid; grid-template-columns:2fr 1fr; gap:var(--sp-6);">
                
                <!-- Left: Upload List -->
                <div class="dashboard-widget" style="overflow-x:auto;">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">Indexed Materials</h3>
                    <?php if (empty($materials)): ?>
                        <p class="text-muted text-sm py-4 text-center">No reference material indexed for AI study tutor. Use form to load syllabus data.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Material Title</th>
                                    <th>File References</th>
                                    <th>Indexed Time</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materials as $mat): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($mat['title']) ?></strong></td>
                                        <td class="font-mono-data text-muted text-xs"><?= htmlspecialchars($mat['file_path']) ?></td>
                                        <td class="font-mono-data text-muted" data-date="<?= $mat['uploaded_at'] ?>" data-date-style="datetime"></td>
                                        <td style="text-align:right;">
                                            <form action="/lecturer/course-builder.php" method="POST" style="margin:0;" onsubmit="return confirm('Remove material context from RAG memory?')">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="action" value="delete_material">
                                                <input type="hidden" name="course_id" value="<?= $course_id ?>">
                                                <input type="hidden" name="material_id" value="<?= $mat['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Right: Upload Form -->
                <div class="dashboard-widget">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">Index Document</h3>
                    <form action="/lecturer/course-builder.php" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:16px;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="upload_material">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">

                        <div class="input-group">
                            <label for="mat-title">Document Title</label>
                            <input type="text" id="mat-title" name="title" required placeholder="e.g. Introduction to Binary Search Trees" class="input-field">
                        </div>

                        <div class="input-group">
                            <label for="mat-file">File Upload (.txt / PDF / DOCX)</label>
                            <input type="file" id="mat-file" name="material_file" class="input-field" style="padding: 10px;">
                        </div>

                        <div class="input-group">
                            <label for="mat-text">Direct Text Context</label>
                            <textarea id="mat-text" name="content_text" placeholder="Copy syllabus definitions or chapter notes directly here for rapid keyword scans..." class="input-field"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">Index Syllabus Material</button>
                    </form>
                </div>
            </div>

            <!-- ── TAB 2: SYLLABUS LESSONS ── -->
            <div id="tab-lessons" class="tab-content" style="display:none; grid-template-columns:2fr 1fr; gap:var(--sp-6);">
                <!-- Left: Syllabus list -->
                <div class="dashboard-widget" style="overflow-x:auto;">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">Syllabus Modules</h3>
                    <?php if (empty($lessons)): ?>
                        <p class="text-muted text-sm py-4 text-center">No syllabus lessons structured yet. Create modules using the form.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Sequence</th>
                                    <th>Module Title</th>
                                    <th style="text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lessons as $les): ?>
                                    <tr>
                                        <td class="font-mono-data text-muted">Module #<?= $les['sequence_order'] ?></td>
                                        <td><strong><?= htmlspecialchars($les['title']) ?></strong></td>
                                        <td style="text-align:right;">
                                            <form action="/lecturer/course-builder.php" method="POST" style="margin:0;" onsubmit="return confirm('Delete this lesson module? (Will break student tracking progress)')">
                                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                                <input type="hidden" name="action" value="delete_lesson">
                                                <input type="hidden" name="course_id" value="<?= $course_id ?>">
                                                <input type="hidden" name="lesson_id" value="<?= $les['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Right: Create Lesson Form -->
                <div class="dashboard-widget">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">Create Syllabus Module</h3>
                    <form action="/lecturer/course-builder.php" method="POST" style="display:flex; flex-direction:column; gap:16px;">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="add_lesson">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">

                        <div class="input-group">
                            <label for="les-title">Lesson Title</label>
                            <input type="text" id="les-title" name="title" required placeholder="e.g. Binary Tree Traversals" class="input-field">
                        </div>

                        <div class="input-group">
                            <label for="les-seq">Sequence Order</label>
                            <input type="number" id="les-seq" name="sequence_order" value="1" required class="input-field">
                        </div>

                        <div class="input-group">
                            <label for="les-std">📖 Standard Mode Content</label>
                            <textarea id="les-std" name="content_standard" required placeholder="Detailed lecture descriptions for average assimilation learners..." class="input-field" style="min-height:100px;"></textarea>
                        </div>

                        <div class="input-group">
                            <label for="les-exp">⚡ Express Mode Content</label>
                            <textarea id="les-exp" name="content_express" required placeholder="Bite-sized bullet points and summaries..." class="input-field" style="min-height:80px;"></textarea>
                        </div>

                        <div class="input-group">
                            <label for="les-deep">🔬 Deep-Dive Mode Content</label>
                            <textarea id="les-deep" name="content_deep" required placeholder="Granular mathematical context or extra laboratory case details..." class="input-field" style="min-height:100px;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">Insert Syllabus Module</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-' + tab).style.display = 'grid';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
