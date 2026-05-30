<?php
// ============================================================
// ALMS — Student Courses Portal
// ============================================================
$currentPage = 'courses';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$db = db();
// 1. Fetch Student Profile
$stmt = $db->prepare("
    SELECT s.level_id, s.department_id, d.name AS department_name
    FROM student_profiles s
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

$level_name = ($profile['level_id'] <= 2) ? 'ND' : 'HND';

// 2. Query Courses matching department & level
$coursesStmt = $db->prepare("
    SELECT c.id, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id WHERE lp.student_id = ? AND l.course_id = c.id) AS completed_lessons,
           (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons
    FROM courses c
    WHERE c.department_id = ? AND c.level = ?
    ORDER BY c.course_code ASC
");
$coursesStmt->execute([$_SESSION['user_id'], $profile['department_id'], $level_name]);
$courses = $coursesStmt->fetchAll();

$pageTitle = 'My Course Portfolios';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <div style="margin-bottom: var(--sp-6);">
            <h2 style="font-size: 1.5rem; font-weight:700;">My Active Courses</h2>
            <p class="text-secondary text-sm">Review registered curriculum modules, syllabus track progression, and launch study guides.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div class="empty-state card-flat">
                <div class="empty-state-icon">📚</div>
                <div class="empty-state-title">No Enrolled Courses</div>
                <p class="empty-state-desc">Your department hasn't published active curriculum paths for this level yet.</p>
            </div>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
                <?php foreach ($courses as $c): 
                    $pct = $c['total_lessons'] > 0 ? round(($c['completed_lessons'] / $c['total_lessons']) * 100) : 0;
                ?>
                <div class="course-card">
                    <div class="course-card-header">
                        <div class="course-card-icon">📖</div>
                        <span class="badge badge-neutral"><?= htmlspecialchars($level_name) ?> Level</span>
                    </div>
                    <div>
                        <div class="course-card-code"><?= htmlspecialchars($c['course_code']) ?></div>
                        <h3 class="course-card-title"><?= htmlspecialchars($c['course_name']) ?></h3>
                    </div>

                    <!-- Progress bar -->
                    <div style="display:flex; flex-direction:column; gap:4px; margin-top:8px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--clr-text-secondary);">
                            <span>Module Progress</span>
                            <span class="font-mono-data"><?= $pct ?>%</span>
                        </div>
                        <div class="progress-bar"><div class="progress-bar-fill" style="width:<?= $pct ?>%;"></div></div>
                    </div>

                    <div style="border-top:1px solid var(--clr-border-light); padding-top:12px; display:flex; justify-content:space-between; align-items:center; font-size:0.8125rem;">
                        <span class="text-muted"><?= $c['completed_lessons'] ?> / <?= $c['total_lessons'] ?> modules</span>
                        <a href="/student/lesson.php?course_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm" style="border-radius:12px; padding: 6px 12px;">Launch Player &rarr;</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
