<?php
// ============================================================
// ALMS — Student Dashboard
// ============================================================
$currentPage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

// 1. Fetch Student Profile
$db = db();
$stmt = $db->prepare("
    SELECT s.matric_number, s.level_id, s.who5_score, s.vark_style, s.current_pace, s.onboarded, d.name AS department_name, s.department_id
    FROM student_profiles s
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if (!$profile || $profile['onboarded'] == 0) {
    header('Location: /student/onboarding.php');
    exit;
}

// 2. Fetch Latest Wellness Log
$wellnessStmt = $db->prepare("SELECT attention_span, stress_level FROM student_wellness_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT 1");
$wellnessStmt->execute([$_SESSION['user_id']]);
$wellnessLog = $wellnessStmt->fetch();

$currentAttention = $wellnessLog['attention_span'] ?? 'medium';
$currentStress = $wellnessLog['stress_level'] ?? 'low';

// 3. Fetch Courses for this department & level
$level_name = ($profile['level_id'] <= 2) ? 'ND' : 'HND';
$coursesStmt = $db->prepare("
    SELECT c.id, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id WHERE lp.student_id = ? AND l.course_id = c.id) AS completed_lessons,
           (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) AS total_lessons
    FROM courses c
    WHERE c.department_id = ? AND c.level = ?
");
$coursesStmt->execute([$_SESSION['user_id'], $profile['department_id'], $level_name]);
$courses = $coursesStmt->fetchAll();

// Compute overall progress
$totalAllLessons = 0;
$completedAllLessons = 0;
foreach ($courses as $c) {
    $totalAllLessons += $c['total_lessons'];
    $completedAllLessons += $c['completed_lessons'];
}
$overallProgress = $totalAllLessons > 0 ? round(($completedAllLessons / $totalAllLessons) * 100) : 0;

// 4. Fetch Upcoming Assignments
$assignmentsStmt = $db->prepare("
    SELECT a.id, a.title, a.due_date, c.course_code,
           (SELECT id FROM assignment_submissions WHERE assignment_id = a.id AND student_id = ?) AS submission_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE c.department_id = ? AND c.level = ? AND a.due_date >= NOW()
    ORDER BY a.due_date ASC LIMIT 5
");
$assignmentsStmt->execute([$_SESSION['user_id'], $profile['department_id'], $level_name]);
$assignments = $assignmentsStmt->fetchAll();

// 5. Generate mock/real attendance records
$today = (int)date('d');
$daysInMonth = (int)date('t');
$startOfWeek = (int)date('w', strtotime(date('Y-m-01'))); // 0 = Sunday, 6 = Saturday

// Simulating attendance values: 80% present, 5% excused, 10% absent, 5% future
$attendanceSummary = ['present' => 0, 'excused' => 0, 'absent' => 0];
$attendanceDays = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    if ($day > $today) {
        $status = 'future';
    } else {
        // Pseudo-random but stable status per day for demonstration
        $seed = ($day * 13) % 100;
        if ($seed < 82) {
            $status = 'present';
            $attendanceSummary['present']++;
        } elseif ($seed < 90) {
            $status = 'excused';
            $attendanceSummary['excused']++;
        } else {
            $status = 'absent';
            $attendanceSummary['absent']++;
        }
    }
    $attendanceDays[$day] = $status;
}
$attendancePercentage = ($attendanceSummary['present'] + $attendanceSummary['absent']) > 0 
    ? round(($attendanceSummary['present'] / ($attendanceSummary['present'] + $attendanceSummary['absent'])) * 100) 
    : 100;

// Page config
$pageTitle = 'Student Command Center';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Welcome Banner with Badges -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: var(--sp-6);">
            <div>
                <p class="text-secondary" style="font-size: 0.9375rem;"><?= htmlspecialchars($profile['department_name'] ?? 'General Studies') ?> &middot; <?= htmlspecialchars($level_name) ?> Level</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <span class="tag-badge tag-vark-<?= strtolower($profile['vark_style'] ?? 'r') ?>">VARK: <?= htmlspecialchars(strtoupper($profile['vark_style'] ?? 'R')) ?> style</span>
                <span class="tag-badge tag-pace-<?= strtolower($profile['current_pace'] ?? 'standard') ?>">Pace: <?= htmlspecialchars(ucfirst($profile['current_pace'] ?? 'standard')) ?></span>
            </div>
        </div>

        <!-- ── Quick Wellness Pulse Logger ── -->
        <div class="dashboard-widget mb-6" style="background: var(--clr-surface); border: 1px solid var(--clr-border-light);">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 4px;">Wellness Pulse Check</h3>
                    <p class="text-secondary" style="font-size: 0.8125rem; margin-bottom: 0;">Logging how you feel dynamically adjusts learning pacing.</p>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center;">
                    <div style="display: flex; gap: 8px; items-center;">
                        <span class="text-xs font-bold text-secondary uppercase" style="letter-spacing: 0.05em;">Attention:</span>
                        <div style="display: flex; gap: 4px;">
                            <button class="wellness-btn level-low <?= $currentAttention === 'low' ? 'selected' : '' ?>" onclick="logWellness('attention', 'low', this)">Low</button>
                            <button class="wellness-btn level-medium <?= $currentAttention === 'medium' ? 'selected' : '' ?>" onclick="logWellness('attention', 'medium', this)">Med</button>
                            <button class="wellness-btn level-high <?= $currentAttention === 'high' ? 'selected' : '' ?>" onclick="logWellness('attention', 'high', this)">High</button>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; items-center;">
                        <span class="text-xs font-bold text-secondary uppercase" style="letter-spacing: 0.05em;">Stress:</span>
                        <div style="display: flex; gap: 4px;">
                            <button class="wellness-btn level-low <?= $currentStress === 'low' ? 'selected' : '' ?>" onclick="logWellness('stress', 'low', this)">Low</button>
                            <button class="wellness-btn level-medium <?= $currentStress === 'medium' ? 'selected' : '' ?>" onclick="logWellness('stress', 'medium', this)">Med</button>
                            <button class="wellness-btn level-high <?= $currentStress === 'high' ? 'selected' : '' ?>" onclick="logWellness('stress', 'high', this)">High</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Stats Grid Row ── -->
        <div class="stats-row mb-6">
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= count($courses) ?></div>
                    <div class="stat-card-label">Courses Active</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <?php
                    $pendingAssignmentsCount = 0;
                    foreach ($assignments as $a) {
                        if (!$a['submission_id']) $pendingAssignmentsCount++;
                    }
                    ?>
                    <div class="stat-card-value font-mono-data"><?= $pendingAssignmentsCount ?></div>
                    <div class="stat-card-label">Pending Duties</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon amber">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $attendancePercentage ?>%</div>
                    <div class="stat-card-label">Attendance Pulse</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon red">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <div class="stat-card-value font-mono-data">3.82</div>
                    <div class="stat-card-label">Academic CGPA</div>
                </div>
            </div>
        </div>

        <!-- ── Main Grid ── -->
        <div class="content-grid">
            
            <!-- Left Grid Pane -->
            <div style="display: flex; flex-direction: column; gap: var(--sp-6);">
                
                <!-- Academic Progress Panel -->
                <div class="dashboard-widget">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: var(--sp-4);">Academic Progress</h3>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 40px; align-items: center; justify-content: space-around; margin-bottom: var(--sp-6);">
                        <!-- SVG Radial Progress Ring -->
                        <div style="position: relative; width: 140px; height: 140px;">
                            <svg width="140" height="140" viewBox="0 0 140 140">
                                <circle cx="70" cy="70" r="58" stroke="var(--clr-border-light)" stroke-width="8" fill="none"></circle>
                                <circle id="radial-progress-bar" cx="70" cy="70" r="58" stroke="var(--clr-primary)" stroke-width="8" fill="none"
                                        class="progress-ring-circle" stroke-dasharray="364.4" stroke-dashoffset="364.4"></circle>
                            </svg>
                            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <span class="font-mono-data" style="font-size: 1.75rem; font-weight: 800; color: var(--clr-text); line-height: 1;"><?= $overallProgress ?>%</span>
                                <span class="text-xs text-muted" style="margin-top: 4px;">Completed</span>
                            </div>
                        </div>

                        <!-- Course Bars -->
                        <div style="flex: 1; display: flex; flex-direction: column; gap: 16px; min-width: 260px;">
                            <?php if (empty($courses)): ?>
                                <p class="text-muted" style="font-size: 0.875rem;">No courses registered in this level.</p>
                            <?php else: ?>
                                <?php foreach ($courses as $c): 
                                    $cProgress = $c['total_lessons'] > 0 ? round(($c['completed_lessons'] / $c['total_lessons']) * 100) : 0;
                                ?>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; font-weight: 600;">
                                        <span><?= htmlspecialchars($c['course_code']) ?> &middot; <?= htmlspecialchars($c['course_name']) ?></span>
                                        <span class="font-mono-data text-primary"><?= $cProgress ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill" style="width: 0%;" data-target-width="<?= $cProgress ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Assignments/Tasks -->
                <div class="dashboard-widget">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: var(--sp-4);">Upcoming Deadlines</h3>
                    <?php if (empty($assignments)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-title">No Pending Assignments</div>
                            <p class="empty-state-desc">You are fully caught up with all deadlines in this term!</p>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($assignments as $a): 
                                $isSubmitted = !empty($a['submission_id']);
                            ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: var(--sp-3) var(--sp-4); border-radius: var(--radius-md); border: 1px solid var(--clr-border-light); background: var(--clr-bg);">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 600; font-size: 0.9375rem;"><?= htmlspecialchars($a['title']) ?></span>
                                    <span class="text-xs text-muted"><?= htmlspecialchars($a['course_code']) ?> &middot; Due <span data-date="<?= $a['due_date'] ?>" data-date-style="datetime"></span></span>
                                </div>
                                <div>
                                    <?php if ($isSubmitted): ?>
                                        <span class="badge badge-green">Submitted</span>
                                    <?php else: ?>
                                        <span class="badge badge-amber">Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Grid Pane -->
            <div style="display: flex; flex-direction: column; gap: var(--sp-6);">
                
                <!-- Attendance Pulse Widget -->
                <div class="dashboard-widget">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: var(--sp-4);">
                        <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 0;">Attendance Pulse</h3>
                        <span class="text-xs font-mono-data text-muted"><?= date('F Y') ?></span>
                    </div>

                    <div class="attendance-grid">
                        <!-- Headers -->
                        <div class="attendance-dot header">S</div>
                        <div class="attendance-dot header">M</div>
                        <div class="attendance-dot header">T</div>
                        <div class="attendance-dot header">W</div>
                        <div class="attendance-dot header">T</div>
                        <div class="attendance-dot header">F</div>
                        <div class="attendance-dot header">S</div>

                        <!-- Offsets before start of month -->
                        <?php for ($o = 0; $o < $startOfWeek; $o++): ?>
                            <div></div>
                        <?php endfor; ?>

                        <!-- Calendar Days -->
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                            $status = $attendanceDays[$d];
                            $isToday = ($d === (int)date('d'));
                        ?>
                            <div class="attendance-dot <?= $status ?> <?= $isToday ? 'today' : '' ?>" title="Day <?= $d ?>: <?= ucfirst($status) ?>">
                                <?= $d ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- AI Assistant Widget -->
                <div class="dashboard-widget" style="background: var(--clr-primary); color: #FFFFFF; position: relative; overflow: hidden; border: none;">
                    <div style="position: absolute; top: -20px; right: -20px; opacity: 0.15; font-size: 6rem; pointer-events: none;">🧠</div>
                    <div style="position: relative; z-index: 2;">
                        <span class="badge" style="background: rgba(255,255,255,0.2); color: #FFFFFF; font-size: 0.625rem; margin-bottom: var(--sp-3);">Adaptive Assistant</span>
                        
                        <?php
                        // Adaptive notification message based on logged stress/attention values
                        $aiPrompt = 'Ready to study? Ask me anything about your current courses.';
                        if ($currentStress === 'high') {
                            $aiPrompt = 'Taking it easy today? Ask me to summarize lessons into brief outlines.';
                        } elseif ($currentAttention === 'low') {
                            $aiPrompt = 'Need support? Let’s try some interactive practice questions.';
                        }
                        ?>
                        <h4 style="color: #FFFFFF; font-size: 1.125rem; margin-bottom: 8px; line-height: 1.3; font-weight: 700;"><?= $aiPrompt ?></h4>
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.8125rem; margin-bottom: var(--sp-4);">I have loaded your VARK learning preferences and pacing indexes.</p>
                        
                        <a href="/student/ai-assistant.php" class="btn btn-sm" style="background: #FFFFFF; color: var(--clr-primary); font-weight: 700; border: none;">Start Session</a>
                    </div>
                </div>

                <!-- Announcements / RAG activity logs -->
                <div class="dashboard-widget">
                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: var(--sp-4);">Announcements</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.8125rem;">
                        <div style="border-bottom: 1px solid var(--clr-border-light); padding-bottom: 10px;">
                            <span class="badge badge-red" style="margin-bottom: 4px;">Urgent</span>
                            <div style="font-weight: 600; color: var(--clr-text); margin-bottom: 2px;">VARK Profile Setup Required</div>
                            <p class="text-secondary">Please complete your onboarding questionnaire in full to configure AI agents.</p>
                        </div>
                        <div>
                            <span class="badge badge-neutral" style="margin-bottom: 4px;">System</span>
                            <div style="font-weight: 600; color: var(--clr-text); margin-bottom: 2px;">ALMS Portal Migrated</div>
                            <p class="text-secondary">ALMS framework migration has finished successfully. Database synced.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<!-- Extra JS to animate dashboard radial progress and bars -->
<?php
$extraJs = '
<script>
document.addEventListener("DOMContentLoaded", () => {
    // 1. Animate Radial Circle
    const radial = document.getElementById("radial-progress-bar");
    if (radial) {
        const percentage = ' . $overallProgress . ';
        const radius = 58;
        const circumference = 2 * Math.PI * radius; // 364.4
        const offset = circumference - (percentage / 100) * circumference;
        
        gsap.to(radial, {
            strokeDashoffset: offset,
            duration: 1.2,
            ease: "power2.out"
        });
    }

    // 2. Animate progress bar fills
    document.querySelectorAll(".progress-bar-fill").forEach(bar => {
        const target = bar.getAttribute("data-target-width");
        gsap.to(bar, {
            width: target,
            duration: 1.0,
            ease: "power2.out",
            delay: 0.2
        });
    });
});

function logWellness(type, level, btn) {
    // Select siblings
    const siblings = btn.parentElement.querySelectorAll(".wellness-btn");
    siblings.forEach(b => b.classList.remove("selected"));
    btn.classList.add("selected");

    // Retrieve both settings
    let attention = "medium";
    let stress = "low";

    document.querySelectorAll(".wellness-btn.selected").forEach(b => {
        const val = b.textContent.trim().toLowerCase();
        if (b.closest("div").previousElementSibling.textContent.includes("Attention")) {
            attention = val === "med" ? "medium" : val;
        } else {
            stress = val === "med" ? "medium" : val;
        }
    });

    // Post to database
    fetch("/api/wellness-save.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": document.querySelector(\'meta[name="csrf-token"]\')?.content || ""
        },
        body: JSON.stringify({
            attention_span: attention,
            stress_level: stress
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.error("Wellness log failed:", data);
        }
    })
    .catch(err => console.error("Wellness pulse network error:", err));
}
</script>
';
require_once __DIR__ . '/../includes/footer.php';
?>
