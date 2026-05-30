<?php
// ============================================================
// ALMS — Student Quiz Portal
// ============================================================
$currentPage = 'quizzes';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$db = db();

// 1. Fetch Student Profile
$stmt = $db->prepare("SELECT level_id, department_id FROM student_profiles WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

$level_name = ($profile['level_id'] <= 2) ? 'ND' : 'HND';

// 2. Fetch Quizzes and attempts
$quizzesStmt = $db->prepare("
    SELECT q.id, q.title, q.description, q.max_points, c.course_code,
           (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? ORDER BY completed_at DESC LIMIT 1) AS attempt_score,
           (SELECT percentage FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? ORDER BY completed_at DESC LIMIT 1) AS attempt_pct
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE c.department_id = ? AND c.level = ?
    ORDER BY q.created_at DESC
");
$quizzesStmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $profile['department_id'], $level_name]);
$quizzes = $quizzesStmt->fetchAll();

// Select active quiz to attempt
$active_quiz_id = (int)($_GET['quiz_id'] ?? 0);
$quizData = null;
$questions = [];

if ($active_quiz_id > 0) {
    // Verify quiz exists and matches dept
    $qCheck = $db->prepare("
        SELECT q.id, q.title, q.description, c.course_code 
        FROM quizzes q 
        JOIN courses c ON q.course_id = c.id 
        WHERE q.id = ? AND c.department_id = ? AND c.level = ?
    ");
    $qCheck->execute([$active_quiz_id, $profile['department_id'], $level_name]);
    $quizData = $qCheck->fetch();

    if ($quizData) {
        // Fetch questions
        $questStmt = $db->prepare("SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE quiz_id = ?");
        $questStmt->execute([$active_quiz_id]);
        $questions = $questStmt->fetchAll();
    }
}

$pageTitle = 'Quizzes Portal';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in">
        
        <?php if ($quizData && !empty($questions)): ?>
            <!-- ── QUIZ MCQ ACTIVE ATTEMPT PLAYER ── -->
            <div style="margin-bottom:var(--sp-4);">
                <a href="/student/quiz.php" style="font-size:0.8125rem; font-weight:600;">&larr; Exit Quiz</a>
                <h2 style="font-size: 1.5rem; font-weight:700; margin-top:4px;"><?= htmlspecialchars($quizData['title']) ?></h2>
                <p class="text-secondary text-sm"><?= htmlspecialchars($quizData['course_code']) ?> &middot; Choose correct options sequentially.</p>
            </div>

            <div class="card-flat" id="quiz-player-card" style="max-width:680px; margin:0 auto; padding:var(--sp-8); background:var(--clr-surface); border:1px solid var(--clr-border-light);">
                
                <!-- Progress bar -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:var(--sp-6);">
                    <span class="text-xs text-muted uppercase font-bold" id="quiz-question-counter">Question 1 of <?= count($questions) ?></span>
                    <div style="width:160px; height:8px; background:var(--clr-bg); border-radius:4px; overflow:hidden;">
                        <div id="quiz-progress-fill" style="width: <?= 100 / count($questions) ?>%; height:100%; background:var(--clr-primary); transition:width 0.4s ease;"></div>
                    </div>
                </div>

                <div id="questions-wrapper">
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="quiz-question-slide" id="q-slide-<?= $index ?>" style="display: <?= $index === 0 ? 'block' : 'none' ?>;">
                            <h3 style="font-size:1.25rem; font-weight:700; margin-bottom:var(--sp-6); line-height:1.4;">
                                <?= htmlspecialchars($q['question_text']) ?>
                            </h3>
                            
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <label class="quiz-option" onclick="selectMcqOption(<?= $index ?>, 'A')">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="A" style="display:none;">
                                    <div class="quiz-option-marker">A</div>
                                    <span><?= htmlspecialchars($q['option_a']) ?></span>
                                </label>
                                
                                <label class="quiz-option" onclick="selectMcqOption(<?= $index ?>, 'B')">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="B" style="display:none;">
                                    <div class="quiz-option-marker">B</div>
                                    <span><?= htmlspecialchars($q['option_b']) ?></span>
                                </label>
                                
                                <label class="quiz-option" onclick="selectMcqOption(<?= $index ?>, 'C')">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="C" style="display:none;">
                                    <div class="quiz-option-marker">C</div>
                                    <span><?= htmlspecialchars($q['option_c']) ?></span>
                                </label>
                                
                                <label class="quiz-option" onclick="selectMcqOption(<?= $index ?>, 'D')">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="D" style="display:none;">
                                    <div class="quiz-option-marker">D</div>
                                    <span><?= htmlspecialchars($q['option_d']) ?></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navigation -->
                <div style="display:flex; justify-content:space-between; margin-top:40px; border-top:1px solid var(--clr-border-light); padding-top:20px;">
                    <button class="btn btn-outline btn-sm" id="quiz-prev-btn" onclick="prevQuizQuestion()" style="visibility:hidden;">Previous</button>
                    <button class="btn btn-primary btn-sm" id="quiz-next-btn" onclick="nextQuizQuestion()">Next Question</button>
                    <button class="btn btn-primary btn-sm animate-pulse-glow" id="quiz-submit-btn" onclick="submitQuizAnswers(<?= $active_quiz_id ?>)" style="display:none;">Submit Responses</button>
                </div>
            </div>

            <!-- ── QUIZ RESULTS REPORT CARD (HIDDEN BY DEFAULT) ── -->
            <div class="card-flat text-center" id="quiz-results-card" style="display:none; max-width:500px; margin:40px auto 0; padding:var(--sp-8); background:var(--clr-surface); border:1px solid var(--clr-border-light);">
                <div style="font-size:4rem; margin-bottom:var(--sp-4);" id="result-emoji">🎉</div>
                <h3 style="font-size:1.5rem; font-weight:800;" id="result-title">Quiz Completed</h3>
                <div style="font-size:2.5rem; font-weight:800; color:var(--clr-primary); margin: 20px 0;" class="font-mono-data" id="result-percentage">80%</div>
                <p class="text-secondary" style="line-height:1.6; margin-bottom:var(--sp-6);" id="result-feedback">Excellent job!</p>
                <a href="/student/quiz.php" class="btn btn-outline">Close Portal</a>
            </div>

        <?php else: ?>
            <!-- ── QUIZ LISTINGS INDEX ── -->
            <div style="margin-bottom: var(--sp-6);">
                <h2 style="font-size: 1.5rem; font-weight:700;">Curriculum Quizzes</h2>
                <p class="text-secondary text-sm">Attempt interactive assessments created by lecturers to test understanding.</p>
            </div>

            <?php if (empty($quizzes)): ?>
                <div class="empty-state card-flat">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title">No Quizzes Active</div>
                    <p class="empty-state-desc">Your department hasn't active tests published for this curriculum path yet.</p>
                </div>
            <?php else: ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
                    <?php foreach ($quizzes as $q): 
                        $attempted = ($q['attempt_score'] !== null);
                    ?>
                    <div class="card-flat" style="border: 1px solid var(--clr-border-light); background: var(--clr-surface); display:flex; flex-direction:column; justify-content:space-between; gap:16px;">
                        <div>
                            <span class="badge badge-neutral" style="margin-bottom:4px;"><?= htmlspecialchars($q['course_code']) ?></span>
                            <h3 style="font-size:1.125rem; font-weight:700; margin-bottom:8px;"><?= htmlspecialchars($q['title']) ?></h3>
                            <p class="text-secondary" style="font-size:0.8125rem; margin:0; line-height:1.5;"><?= htmlspecialchars($q['description']) ?></p>
                        </div>

                        <div style="border-top:1px solid var(--clr-border-light); padding-top:12px; display:flex; justify-content:space-between; align-items:center; font-size:0.8125rem;">
                            <div>
                                <?php if ($attempted): ?>
                                    <span class="badge badge-green" style="font-weight:700;">Score: <?= $q['attempt_score'] ?> pts (<?= round($q['attempt_pct']) ?>%)</span>
                                <?php else: ?>
                                    <span class="text-muted"><?= $q['max_points'] ?> max points</span>
                                <?php endif; ?>
                            </div>
                            <a href="/student/quiz.php?quiz_id=<?= $q['id'] ?>" class="btn btn-primary btn-sm" style="border-radius:12px; padding: 6px 12px;">
                                <?= $attempted ? 'Retake Quiz' : 'Start Quiz' ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<script>
let currentQuestionIndex = 0;
const totalQuestions = <?= count($questions) ?>;

function selectMcqOption(slideIndex, val) {
    const slide = document.getElementById('q-slide-' + slideIndex);
    const options = slide.querySelectorAll('.quiz-option');
    options.forEach(opt => opt.classList.remove('selected'));
    
    // Check radio matching val
    const optLabel = Array.from(options).find(opt => opt.querySelector('input').value === val);
    if (optLabel) {
        optLabel.classList.add('selected');
        optLabel.querySelector('input').checked = true;
    }
}

function prevQuizQuestion() {
    if (currentQuestionIndex > 0) {
        document.getElementById('q-slide-' + currentQuestionIndex).style.display = 'none';
        currentQuestionIndex--;
        document.getElementById('q-slide-' + currentQuestionIndex).style.display = 'block';
        updateQuizControls();
    }
}

function nextQuizQuestion() {
    // Validate that option is selected before moving
    const activeSlide = document.getElementById('q-slide-' + currentQuestionIndex);
    const checked = activeSlide.querySelector('input[type="radio"]:checked');
    if (!checked) {
        alert('Please choose an answer option first.');
        return;
    }

    if (currentQuestionIndex < totalQuestions - 1) {
        document.getElementById('q-slide-' + currentQuestionIndex).style.display = 'none';
        currentQuestionIndex++;
        document.getElementById('q-slide-' + currentQuestionIndex).style.display = 'block';
        updateQuizControls();
    }
}

function updateQuizControls() {
    // Update labels and progress
    document.getElementById('quiz-question-counter').textContent = `Question ${currentQuestionIndex + 1} of ${totalQuestions}`;
    document.getElementById('quiz-progress-fill').style.width = `${((currentQuestionIndex + 1) / totalQuestions) * 100}%`;

    // Toggle nav buttons
    if (currentQuestionIndex === 0) {
        document.getElementById('quiz-prev-btn').style.visibility = 'hidden';
    } else {
        document.getElementById('quiz-prev-btn').style.visibility = 'visible';
    }

    if (currentQuestionIndex === totalQuestions - 1) {
        document.getElementById('quiz-next-btn').style.display = 'none';
        document.getElementById('quiz-submit-btn').style.display = 'inline-flex';
    } else {
        document.getElementById('quiz-next-btn').style.display = 'inline-flex';
        document.getElementById('quiz-submit-btn').style.display = 'none';
    }
}

function submitQuizAnswers(quiz_id) {
    // Last question check
    const activeSlide = document.getElementById('q-slide-' + currentQuestionIndex);
    const checked = activeSlide.querySelector('input[type="radio"]:checked');
    if (!checked) {
        alert('Please choose an answer option first.');
        return;
    }

    const answers = [];
    document.querySelectorAll('.quiz-question-slide').forEach((slide, index) => {
        const input = slide.querySelector('input[type="radio"]:checked');
        if (input) {
            const qid = parseInt(input.name.match(/\d+/)[0]);
            answers.push({
                question_id: qid,
                selected_option: input.value
            });
        }
    });

    const submitBtn = document.getElementById('quiz-submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Scoring quiz...';

    fetch("/api/quiz-submit.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')?.content || ""
        },
        body: JSON.stringify({
            quiz_id: quiz_id,
            answers: answers
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hide player card, show results
            document.getElementById('quiz-player-card').style.display = 'none';
            
            const resultsCard = document.getElementById('quiz-results-card');
            document.getElementById('result-percentage').textContent = `${Math.round(data.percentage)}%`;
            document.getElementById('result-feedback').textContent = data.feedback;
            
            if (data.passed) {
                document.getElementById('result-emoji').textContent = '🎉';
                document.getElementById('result-title').textContent = 'Quiz Completed Successfully';
            } else {
                document.getElementById('result-emoji').textContent = '📚';
                document.getElementById('result-title').textContent = 'Keep Practicing!';
            }
            
            resultsCard.style.display = 'block';
        } else {
            alert('Error submitting responses: ' + data.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Responses';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network connection error.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Responses';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
