<?php
// ============================================================
// ALMS — Lecturer Dashboard
// ============================================================
$currentPage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'lecturer') {
    header('Location: /index.php');
    exit;
}

$db = db();

// 1. Check verified status
$statusStmt = $db->prepare("SELECT status, title, first_name, last_name FROM users WHERE id = ?");
$statusStmt->execute([$_SESSION['user_id']]);
$userStatus = $statusStmt->fetch();

if ($userStatus && $userStatus['status'] === 'pending') {
    // Render pending verification page
    ?>
    <div style="background:var(--clr-bg); display:flex; justify-content:center; align-items:center; min-height:100vh; padding: var(--sp-6);">
        <div class="glass-card-strong text-center" style="max-width:500px; padding:var(--sp-12);">
            <div style="font-size:4rem; margin-bottom:var(--sp-4);">⏳</div>
            <h1 class="hero-text-cinematic mb-4" style="font-size:1.75rem;">Account Verification Pending</h1>
            <p class="text-secondary" style="line-height:1.6; margin-bottom:var(--sp-6);">
                Welcome <?= htmlspecialchars($userStatus['title'] . ' ' . $userStatus['first_name'] . ' ' . $userStatus['last_name']) ?>! Your lecturer registration has been logged. An administrator must verify and approve your staff profile before you can access the curriculum tools.
            </p>
            <a href="/auth.php?action=logout" class="btn btn-outline">Sign Out</a>
        </div>
    </div>
    <?php
    exit;
}

// 2. Fetch Teachable Assigned Courses
$assignedStmt = $db->prepare("
    SELECT lca.id AS assignment_id, c.id AS course_id, c.course_code, c.course_name, c.level, d.name AS dept_name, d.id AS dept_id
    FROM lecturer_course_assignments lca
    JOIN courses c ON lca.course_id = c.id
    JOIN departments d ON lca.department_id = d.id
    WHERE lca.lecturer_id = ?
");
$assignedStmt->execute([$_SESSION['user_id']]);
$assignedCourses = $assignedStmt->fetchAll();

// 3. Compute Aggregate Stats
$studentCount = 0;
$departmentsAssigned = [];
foreach ($assignedCourses as $ac) {
    $departmentsAssigned[] = $ac['dept_id'];
}
$departmentsAssigned = array_unique($departmentsAssigned);

if (!empty($departmentsAssigned)) {
    // Total students matching those departments
    $inClause = implode(',', array_map('intval', $departmentsAssigned));
    $stdStmt = $db->query("SELECT COUNT(*) AS total FROM student_profiles WHERE department_id IN ($inClause)");
    $studentCount = $stdStmt->fetch()['total'];
}

// 4. Fetch Aggregate Anonymized Wellness Insights
$wellnessStats = ['express' => 0, 'standard' => 0, 'deep' => 0];
$stressStats = ['low' => 0, 'medium' => 0, 'high' => 0];
$attentionStats = ['low' => 0, 'medium' => 0, 'high' => 0];

if (!empty($departmentsAssigned)) {
    $inClause = implode(',', array_map('intval', $departmentsAssigned));
    
    // Pace distribution
    $paceStmt = $db->query("
        SELECT current_pace, COUNT(*) as count 
        FROM student_profiles 
        WHERE department_id IN ($inClause) 
        GROUP BY current_pace
    ");
    while ($r = $paceStmt->fetch()) {
        $wellnessStats[$r['current_pace']] = $r['count'];
    }

    // Stress & Attention distribution (logs from last 7 days)
    $logStmt = $db->query("
        SELECT stress_level, attention_span, COUNT(*) as count 
        FROM student_wellness_logs wl
        JOIN student_profiles sp ON wl.user_id = sp.user_id
        WHERE sp.department_id IN ($inClause) AND wl.logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY stress_level, attention_span
    ");
    while ($r = $logStmt->fetch()) {
        $stressStats[$r['stress_level']] += $r['count'];
        $attentionStats[$r['attention_span']] += $r['count'];
    }
}

// Compute percentage allocations
$totalPace = array_sum($wellnessStats);
$totalStress = array_sum($stressStats);
$totalAttention = array_sum($attentionStats);

// 5. Fetch Recent Submissions
$submissions = [];
if (!empty($assignedCourses)) {
    $courseIds = array_column($assignedCourses, 'course_id');
    $crsIn = implode(',', array_map('intval', $courseIds));
    $subStmt = $db->query("
        SELECT s.id, s.grade, s.submitted_at, a.title AS assignment_title, c.course_code, u.first_name, u.last_name
        FROM assignment_submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON s.student_id = u.id
        WHERE c.id IN ($crsIn)
        ORDER BY s.submitted_at DESC LIMIT 5
    ");
    $submissions = $subStmt->fetchAll();
}

$pageTitle = 'Staff Dashboard';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-lecturer.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <!-- Stats Widgets Row -->
        <div class="stats-row mb-6">
            <div class="stat-card">
                <div class="stat-card-icon blue">📖</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= count($assignedCourses) ?></div>
                    <div class="stat-card-label">Active Courses</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-icon green">🎓</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= $studentCount ?></div>
                    <div class="stat-card-label">Teachable Students</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon red">🧠</div>
                <div>
                    <?php
                    // Display high stress ratio if students are logging it
                    $highStressCount = $stressStats['high'];
                    $stressRatio = $totalStress > 0 ? round(($highStressCount / $totalStress) * 100) : 0;
                    ?>
                    <div class="stat-card-value font-mono-data"><?= $stressRatio ?>%</div>
                    <div class="stat-card-label">High Stress Load</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon amber">🔔</div>
                <div>
                    <div class="stat-card-value font-mono-data"><?= count($submissions) ?></div>
                    <div class="stat-card-label">Recent Submissions</div>
                </div>
            </div>
        </div>

        <!-- 2/3 + 1/3 Content Layout -->
        <div class="content-grid">
            
            <!-- Left Pane: Course List & Submissions -->
            <div style="display:flex; flex-direction:column; gap:var(--sp-6);">
                
                <!-- Teachable Course Portfolios -->
                <div class="dashboard-widget">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">My Course Portfolios</h3>
                    <?php if (empty($assignedCourses)): ?>
                        <p class="text-muted text-sm">No courses assigned to your portfolio yet. Contact administrator to assign courses.</p>
                    <?php else: ?>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                            <?php foreach ($assignedCourses as $ac): ?>
                                <div class="course-card">
                                    <div class="course-card-header">
                                        <div class="course-card-icon">📚</div>
                                        <span class="badge badge-neutral"><?= htmlspecialchars($ac['level']) ?></span>
                                    </div>
                                    <div>
                                        <div class="course-card-code"><?= htmlspecialchars($ac['course_code']) ?></div>
                                        <h4 class="course-card-title"><?= htmlspecialchars($ac['course_name']) ?></h4>
                                    </div>
                                    <div style="border-top:1px solid var(--clr-border-light); padding-top:12px; display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; color:var(--clr-text-secondary);">
                                        <span><?= htmlspecialchars($ac['dept_name']) ?></span>
                                        <a href="/lecturer/course-builder.php?course_id=<?= $ac['course_id'] ?>" style="font-weight:600;">Edit Materials &rarr;</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Submissions -->
                <div class="dashboard-widget" style="overflow-x:auto;">
                    <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:var(--sp-4);">Recent Submissions</h3>
                    <?php if (empty($submissions)): ?>
                        <p class="text-muted text-sm">No recent assignments submitted.</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Assignment</th>
                                    <th>Submission Time</th>
                                    <th style="text-align:right;">Grade Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $sub): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']) ?></strong></td>
                                        <td class="font-mono-data"><?= htmlspecialchars($sub['course_code']) ?></td>
                                        <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                                        <td class="font-mono-data text-muted" data-date="<?= $sub['submitted_at'] ?>" data-date-style="datetime"></td>
                                        <td style="text-align:right;">
                                            <?php if ($sub['grade'] !== null): ?>
                                                <span class="badge badge-green"><?= $sub['grade'] ?> points</span>
                                            <?php else: ?>
                                                <span class="badge badge-amber">Ungraded</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Pane: Wellness Aggregations -->
            <div class="dashboard-widget">
                <h3 style="font-size:1.125rem; font-weight:700; margin-bottom: 2px;">Teachable Wellness Trends</h3>
                <p class="text-muted" style="font-size:0.75rem; margin-bottom:var(--sp-6);">Anonymized metrics aggregated from your student cohort.</p>

                <!-- Pace Distribution -->
                <div style="margin-bottom:var(--sp-6);">
                    <h4 style="font-size:0.875rem; font-weight:600; margin-bottom:12px;">Class Pacing Profiles</h4>
                    
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php 
                        $expressPct = $totalPace > 0 ? round(($wellnessStats['express'] / $totalPace) * 100) : 0;
                        $standardPct = $totalPace > 0 ? round(($wellnessStats['standard'] / $totalPace) * 100) : 0;
                        $deepPct = $totalPace > 0 ? round(($wellnessStats['deep'] / $totalPace) * 100) : 0;
                        ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>⚡ Express Mode</span>
                                <span class="font-mono-data"><?= $expressPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill amber" style="width:<?= $expressPct ?>%;"></div></div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>📖 Standard Mode</span>
                                <span class="font-mono-data"><?= $standardPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill blue" style="width:<?= $standardPct ?>%;"></div></div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>🔬 Deep-Dive Mode</span>
                                <span class="font-mono-data"><?= $deepPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill" style="width:<?= $deepPct ?>%;"></div></div>
                        </div>
                    </div>
                </div>

                <!-- Stress Distribution -->
                <div>
                    <h4 style="font-size:0.875rem; font-weight:600; margin-bottom:12px;">Stress Indicator (Last 7 Days)</h4>
                    
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php 
                        $lowStressPct = $totalStress > 0 ? round(($stressStats['low'] / $totalStress) * 100) : 0;
                        $medStressPct = $totalStress > 0 ? round(($stressStats['medium'] / $totalStress) * 100) : 0;
                        $highStressPct = $totalStress > 0 ? round(($stressStats['high'] / $totalStress) * 100) : 0;
                        ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>🟢 Low Stress</span>
                                <span class="font-mono-data"><?= $lowStressPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill green" style="width:<?= $lowStressPct ?>%;"></div></div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>🟡 Moderate Stress</span>
                                <span class="font-mono-data"><?= $medStressPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill amber" style="width:<?= $medStressPct ?>%;"></div></div>
                        </div>

                        <div>
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; margin-bottom:4px;">
                                <span>🔴 High Stress</span>
                                <span class="font-mono-data"><?= $highStressPct ?>%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-bar-fill" style="width:<?= $highStressPct ?>%;"></div></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
