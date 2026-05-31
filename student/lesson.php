<?php
// ============================================================
// ALMS — Adaptive Lesson Player
// ============================================================
$currentPage = 'courses';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$course_id = (int)($_GET['course_id'] ?? 0);
if ($course_id <= 0) {
    header('Location: /student/courses.php');
    exit;
}

$db = db();

// Verify student department matches course
$studentDeptStmt = $db->prepare("SELECT department_id, current_pace FROM student_profiles WHERE user_id = ?");
$studentDeptStmt->execute([$_SESSION['user_id']]);
$studentProfile = $studentDeptStmt->fetch();

$currentPace = $studentProfile['current_pace'] ?? 'standard';

// Fetch Course metadata
$courseStmt = $db->prepare("SELECT id, course_code, course_name FROM courses WHERE id = ?");
$courseStmt->execute([$course_id]);
$course = $courseStmt->fetch();

if (!$course) {
    header('Location: /student/courses.php');
    exit;
}

// Fetch all lessons for course syllabus outline
$lessonsStmt = $db->prepare("
    SELECT l.id, l.title, l.sequence_order,
           (SELECT id FROM lesson_progress WHERE student_id = ? AND lesson_id = l.id) AS is_completed
    FROM lessons l
    WHERE l.course_id = ?
    ORDER BY l.sequence_order ASC
");
$lessonsStmt->execute([$_SESSION['user_id'], $course_id]);
$lessons = $lessonsStmt->fetchAll();

// Select active lesson (default to first uncompleted lesson or first lesson overall)
$active_lesson_id = (int)($_GET['lesson_id'] ?? 0);
$activeLesson = null;

if ($active_lesson_id > 0) {
    foreach ($lessons as $les) {
        if ((int)$les['id'] === $active_lesson_id) {
            $activeLesson = $les;
            break;
        }
    }
}

if (!$activeLesson && !empty($lessons)) {
    // Select first uncompleted
    foreach ($lessons as $les) {
        if (!$les['is_completed']) {
            $activeLesson = $les;
            break;
        }
    }
    // Fallback to first
    if (!$activeLesson) {
        $activeLesson = $lessons[0];
    }
    $active_lesson_id = (int)$activeLesson['id'];
}

// Fetch full details of the active lesson
$lessonData = null;
if ($active_lesson_id > 0) {
    $dataStmt = $db->prepare("SELECT title, content_standard, content_express, content_deep, sequence_order FROM lessons WHERE id = ?");
    $dataStmt->execute([$active_lesson_id]);
    $lessonData = $dataStmt->fetch();
}

$pageTitle = 'Lesson Player: ' . ($lessonData['title'] ?? 'Outline');
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--clr-border-light); padding-bottom:12px; margin-bottom: var(--sp-6);">
            <div>
                <a href="/student/courses.php" style="font-size:0.8125rem; font-weight:600;">&larr; Back to Courses</a>
                <h2 style="font-size: 1.25rem; font-weight:700; margin-top:4px;"><?= htmlspecialchars($course['course_code'] . ' — ' . $course['course_name']) ?></h2>
            </div>
            
            <!-- Pace Switcher tabs -->
            <div class="pace-switcher" style="width: 280px;">
                <button class="pace-tab <?= $currentPace === 'express' ? 'active' : '' ?>" onclick="switchPacing('express')">⚡ Express</button>
                <button class="pace-tab <?= $currentPace === 'standard' ? 'active' : '' ?>" onclick="switchPacing('standard')">📖 Standard</button>
                <button class="pace-tab <?= $currentPace === 'deep' ? 'active' : '' ?>" onclick="switchPacing('deep')">🔬 Deep</button>
            </div>
        </div>

        <?php if (!$lessonData): ?>
            <div class="card-flat text-center py-12">
                <div style="font-size:3rem; margin-bottom:12px;">📁</div>
                <h3>Syllabus is Empty</h3>
                <p class="text-secondary">Lecturer hasn't uploaded lessons for this course yet.</p>
            </div>
        <?php else: ?>
            <div class="content-grid-reverse">
                
                <!-- Left: Syllabus Sidebar Outline -->
                <div class="dashboard-widget" style="height: fit-content;">
                    <h3 style="font-size:1rem; font-weight:700; margin-bottom:16px;">Syllabus Outline</h3>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($lessons as $les): 
                            $isActive = ((int)$les['id'] === $active_lesson_id);
                            $isCompleted = !empty($les['is_completed']);
                        ?>
                            <a href="/student/lesson.php?course_id=<?= $course_id ?>&lesson_id=<?= $les['id'] ?>" 
                               class="sidebar-link <?= $isActive ? 'active' : '' ?>" 
                               style="justify-content:space-between; padding: 10px 12px; background: <?= $isActive ? 'var(--clr-primary-light)' : 'transparent' ?>; border-radius:8px;">
                                <div style="display:flex; align-items:center; gap:8px; overflow:hidden;">
                                    <span style="font-size:0.75rem; font-weight:700; color:var(--clr-text-muted); flex-shrink:0;">#<?= $les['sequence_order'] ?></span>
                                    <span style="font-size:0.875rem; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;"><?= htmlspecialchars($les['title']) ?></span>
                                </div>
                                <div>
                                    <?php if ($isCompleted): ?>
                                        <span style="color:var(--clr-success); font-weight:700;">✓</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Active Lesson Body Content -->
                <div class="dashboard-widget flex flex-col justify-between" style="min-height: 480px; background:var(--clr-surface);">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-4);">
                            <span class="badge badge-neutral">Module <?= $lessonData['sequence_order'] ?></span>
                            <span class="text-xs text-muted" style="text-transform:uppercase; font-weight:700; letter-spacing:0.05em;">Pacing Active: <?= ucfirst($currentPace) ?></span>
                        </div>
                        
                        <h3 style="font-size:1.5rem; font-weight:800; margin-bottom:var(--sp-4);"><?= htmlspecialchars($lessonData['title']) ?></h3>

                        <div style="line-height:1.7; font-size:1rem; color:var(--clr-text); font-family:var(--font-body);" class="markdown-container">
                            <?php
                            // Choose content stream based on pacing preference
                            $body = $lessonData['content_standard'];
                            if ($currentPace === 'express') {
                                $body = $lessonData['content_express'];
                            } elseif ($currentPace === 'deep') {
                                $body = $lessonData['content_deep'];
                            }

                            $lines = preg_split('/\R/', $body);
                            foreach ($lines as $line) {
                                $line = rtrim($line);
                                if ($line === '') {
                                    echo '<br>';
                                    continue;
                                }
                                if (str_starts_with($line, '### ')) {
                                    echo '<h4 style="font-size:1.125rem; font-weight:700; margin-top:20px; margin-bottom:8px;">' . htmlspecialchars(substr($line, 4)) . '</h4>';
                                } elseif (str_starts_with($line, '## ')) {
                                    echo '<h3 style="font-size:1.25rem; font-weight:700; margin-top:24px; margin-bottom:12px;">' . htmlspecialchars(substr($line, 3)) . '</h3>';
                                } else {
                                    echo '<p style="margin-bottom:10px;">' . htmlspecialchars($line) . '</p>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Progress controls -->
                    <div style="border-top:1px solid var(--clr-border-light); padding-top:24px; margin-top:40px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <button id="complete-btn" class="btn btn-primary" onclick="markCompleted(<?= $active_lesson_id ?>)">
                                Complete & Next Module
                            </button>
                        </div>
                        <div style="display:flex; gap:12px;">
                            <a href="/student/ai-assistant.php" class="btn btn-outline">Ask AI Assistant</a>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function switchPacing(pace) {
    fetch("/api/lesson-pace.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({ pace: pace })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(err => console.error(err));
}

function markCompleted(lesson_id) {
    const btn = document.getElementById('complete-btn');
    btn.disabled = true;
    btn.textContent = 'Saving progress...';

    fetch("/api/lesson-progress.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({ lesson_id: lesson_id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.next_lesson_id) {
                window.location.href = "/student/lesson.php?course_id=<?= $course_id ?>&lesson_id=" + data.next_lesson_id;
            } else {
                alert('Course syllabus complete! Congratulations!');
                window.location.href = "/student/courses.php";
            }
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.textContent = 'Complete & Next Module';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
