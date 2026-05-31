<?php
// ============================================================
// ALMS — Student Assignments Desk
// ============================================================
$currentPage = 'assignments';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$db = db();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// 1. Fetch Student Profile
$stmt = $db->prepare("SELECT level_id, department_id FROM student_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

$level_name = ($profile['level_id'] <= 2) ? 'ND' : 'HND';

// ── Action Handlers ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'submit_assignment') {
        $assignment_id = (int)($_POST['assignment_id'] ?? 0);
        $text = trim($_POST['submitted_text'] ?? '');
        
        // File upload handling
        $file_path = '';
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['pdf', 'doc', 'docx', 'zip', 'txt', 'png', 'jpg', 'jpeg'];
            $maxBytes = 8 * 1024 * 1024;
            $originalName = basename($_FILES['assignment_file']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true) || $_FILES['assignment_file']['size'] > $maxBytes) {
                redirect('/student/assignments.php?error=invalid_file');
            }

            $uploadDir = realpath(__DIR__ . '/../uploads/assignments');
            if (!$uploadDir) {
                redirect('/student/assignments.php?error=upload_unavailable');
            }

            $safeName = bin2hex(random_bytes(12)) . '.' . $ext;
            $target = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
            if (!move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                redirect('/student/assignments.php?error=upload_unavailable');
            }
            $file_path = '/uploads/assignments/' . $safeName;
        }

        if ($assignment_id > 0 && (!empty($text) || !empty($file_path))) {
            try {
                // Check if already submitted
                $check = $db->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
                $check->execute([$assignment_id, $_SESSION['user_id']]);
                
                if ($check->fetch()) {
                    redirect('/student/assignments.php?error=already_submitted');
                }

                $stmt = $db->prepare("INSERT INTO assignment_submissions (assignment_id, student_id, file_path, submitted_text) VALUES (?, ?, ?, ?)");
                $stmt->execute([$assignment_id, $_SESSION['user_id'], $file_path, $text]);
                redirect('/student/assignments.php?msg=submitted');
            } catch (PDOException $e) {
                redirect('/student/assignments.php?error=db_error');
            }
        } else {
            redirect('/student/assignments.php?error=missing_fields');
        }
    }
}

// 2. Fetch Assignments & Submissions
$assignmentsStmt = $db->prepare("
    SELECT a.id, a.title, a.description, a.due_date, c.course_code, c.course_name,
           s.file_path, s.submitted_text, s.grade, s.feedback, s.submitted_at
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    LEFT JOIN assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE c.department_id = ? AND c.level = ?
    ORDER BY a.due_date ASC
");
$assignmentsStmt->execute([$_SESSION['user_id'], $profile['department_id'], $level_name]);
$assignments = $assignmentsStmt->fetchAll();

$pageTitle = 'Assignments Portal';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Status Messages -->
        <?php if ($msg === 'submitted'): ?>
            <div class="flash-msg success">Assignment submission uploaded successfully.</div>
        <?php endif; ?>

        <?php if ($error === 'already_submitted'): ?>
            <div class="flash-msg error">You have already submitted this assignment.</div>
        <?php elseif ($error === 'missing_fields'): ?>
            <div class="flash-msg error">Please enter text response or upload a file.</div>
        <?php elseif ($error === 'db_error'): ?>
            <div class="flash-msg error">Database error saving submission.</div>
        <?php elseif ($error === 'invalid_file'): ?>
            <div class="flash-msg error">Upload must be PDF, Word, ZIP, text, PNG, or JPG and no larger than 8 MB.</div>
        <?php elseif ($error === 'upload_unavailable'): ?>
            <div class="flash-msg error">File upload is unavailable. Please try again or submit a text response.</div>
        <?php endif; ?>

        <div style="margin-bottom: var(--sp-6);">
            <h2 style="font-size: 1.5rem; font-weight:700;">Assignments Portal</h2>
            <p class="text-secondary text-sm">Upload assignment sheets, review marks, and view feedback from lecturers.</p>
        </div>

        <?php if (empty($assignments)): ?>
            <div class="empty-state card-flat">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-title">No Assignments Found</div>
                <p class="empty-state-desc">Your department lecturers haven't posted assignments for this curriculum level yet.</p>
            </div>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:20px;">
                <?php foreach ($assignments as $a): 
                    $isSubmitted = !empty($a['submitted_at']);
                    $isGraded = ($a['grade'] !== null);
                    $isOverdue = (strtotime($a['due_date']) < time() && !$isSubmitted);
                ?>
                <div class="card-flat" style="border: 1px solid var(--clr-border-light); background: var(--clr-surface); padding: var(--sp-6); display:flex; flex-direction:column; gap:16px;">
                    
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                        <div>
                            <span class="badge badge-neutral" style="margin-bottom:4px;"><?= htmlspecialchars($a['course_code']) ?> &middot; <?= htmlspecialchars($a['course_name']) ?></span>
                            <h3 style="font-size:1.125rem; font-weight:700; margin:0;"><?= htmlspecialchars($a['title']) ?></h3>
                        </div>
                        <div>
                            <?php if ($isGraded): ?>
                                <span class="badge badge-green">Graded: <?= $a['grade'] ?> pts</span>
                            <?php elseif ($isSubmitted): ?>
                                <span class="badge badge-blue">Submitted</span>
                            <?php elseif ($isOverdue): ?>
                                <span class="badge badge-red">Overdue</span>
                            <?php else: ?>
                                <span class="badge badge-amber">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="text-secondary" style="font-size:0.875rem; margin:0; line-height:1.6;"><?= nl2br(htmlspecialchars($a['description'])) ?></p>

                    <div style="font-size:0.75rem; color:var(--clr-text-muted); display:flex; justify-content:space-between;">
                        <span>Due Date: <span data-date="<?= $a['due_date'] ?>" data-date-style="datetime"></span></span>
                        <?php if ($isSubmitted): ?>
                            <span>Submitted: <span data-date="<?= $a['submitted_at'] ?>" data-date-style="datetime"></span></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isSubmitted): ?>
                        <!-- Submission Review -->
                        <div style="border-top:1px solid var(--clr-border-light); padding-top:12px; margin-top:8px; font-size:0.8125rem; display:flex; flex-direction:column; gap:8px;">
                            <div>
                                <span style="font-weight:700;">Your Submission:</span>
                                <?php if ($a['file_path']): ?>
                                    <a href="<?= htmlspecialchars($a['file_path']) ?>" target="_blank" style="margin-left:8px; text-decoration:underline;">View Uploaded File</a>
                                <?php endif; ?>
                                <?php if ($a['submitted_text']): ?>
                                    <p class="text-muted" style="margin-top:4px; font-style:italic;">"<?= htmlspecialchars($a['submitted_text']) ?>"</p>
                                <?php endif; ?>
                            </div>
                            <?php if ($isGraded): ?>
                                <div style="background:var(--clr-bg); border-radius:8px; padding:10px;">
                                    <span style="font-weight:700; color:var(--clr-primary);">Lecturer Feedback:</span>
                                    <p style="margin-top:2px; font-weight:500; color:var(--clr-text-secondary);"><?= htmlspecialchars($a['feedback'] ?? 'No feedback text provided.') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Submission Form -->
                        <form action="/student/assignments.php" method="POST" enctype="multipart/form-data" style="border-top:1px solid var(--clr-border-light); padding-top:16px; display:flex; flex-direction:column; gap:12px;">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="submit_assignment">
                            <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">

                            <div class="input-group">
                                <label for="sub-text-<?= $a['id'] ?>">Text Response / URL</label>
                                <textarea id="sub-text-<?= $a['id'] ?>" name="submitted_text" placeholder="Type text response or insert submission links here..." class="input-field" style="min-height:80px;"></textarea>
                            </div>

                            <div class="input-group">
                                <label for="sub-file-<?= $a['id'] ?>">Attach File (.pdf, .zip, etc.)</label>
                                <input type="file" id="sub-file-<?= $a['id'] ?>" name="assignment_file" class="input-field" style="padding:10px;">
                            </div>

                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">Submit Assignment</button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
