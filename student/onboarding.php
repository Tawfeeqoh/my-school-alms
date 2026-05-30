<?php
// ============================================================
// ALMS — Student Onboarding Wizard
// ============================================================
require_once __DIR__ . '/../config.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

// Check if student has already completed onboarding
$db = db();
$stmt = $db->prepare("SELECT onboarded, first_name FROM student_profiles JOIN users ON users.id = student_profiles.user_id WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

if ($profile && $profile['onboarded'] == 1) {
    header('Location: /student/dashboard.php');
    exit;
}

$firstName = $profile['first_name'] ?? $_SESSION['first_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Onboarding — ALMS</title>
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <style>
        .onboarding-card {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            position: relative;
            min-height: 480px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
    </style>
</head>
<body class="film-grain" style="background: var(--clr-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: var(--sp-6);">

    <div class="glass-card-strong onboarding-card">
        
        <!-- Step Dot Indicators -->
        <div class="step-dots" style="margin-bottom: var(--sp-6);">
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
            <div class="step-dot"></div>
            <div class="step-dot"></div>
            <div class="step-dot"></div>
        </div>

        <form id="onboarding-form" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
            
            <!-- ── STEP 1: Welcome ── -->
            <div class="onboarding-step active">
                <div style="text-align: center; margin-top: var(--sp-6);">
                    <div style="font-size: 4rem; margin-bottom: var(--sp-4);">👋</div>
                    <h1 class="hero-text-cinematic mb-4" style="font-size: 2rem;">Welcome to ALMS, <?= htmlspecialchars($firstName) ?>!</h1>
                    <p class="text-secondary" style="max-width: 480px; margin: 0 auto; line-height: 1.6;">
                        ALMS is an Advanced Learning Management System designed to conform to your unique learning style, track attention span, support mental wellness, and guide you through courses at your preferred pace.
                    </p>
                </div>
            </div>

            <!-- ── STEP 2: WHO-5 Wellbeing Index ── -->
            <div class="onboarding-step">
                <h2 class="hero-text-cinematic mb-2 text-center" style="font-size: 1.5rem;">How have you been feeling lately?</h2>
                <p class="text-muted text-center mb-6" style="font-size: 0.8125rem;">This helps us customize course materials and intervals to prevent burn-out. (Not a clinical diagnostic tool)</p>
                
                <div style="display: flex; flex-direction: column; gap: var(--sp-6); max-height: 280px; overflow-y: auto; padding-right: 8px;">
                    <?php
                    $questions = [
                        "I have felt cheerful and in good spirits",
                        "I have felt calm and relaxed",
                        "I have felt active and vigorous",
                        "I woke up feeling fresh and rested",
                        "My daily life has been filled with things that interest me"
                    ];
                    foreach ($questions as $i => $q):
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.875rem; font-weight: 600;">
                            <span><?= ($i+1) ?>. <?= $q ?></span>
                            <span id="score-val-<?= $i ?>" class="text-primary font-mono-data">3/5</span>
                        </div>
                        <input type="range" min="0" max="5" value="3" class="who5-slider" name="who5[]" oninput="updateWho5Score(<?= $i ?>, this.value)">
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--clr-text-muted);">
                            <span>At no time 😞</span>
                            <span>All the time 😊</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 20px; text-align: center; font-weight: 700; font-size: 1rem;">
                    Wellness Index: <span id="who5-total" class="text-primary font-mono-data">15</span>/25 
                    <span id="who5-label" style="margin-left: 8px;" class="badge badge-blue">Good balance 😊</span>
                </div>
            </div>

            <!-- ── STEP 3: VARK Learning Style ── -->
            <div class="onboarding-step">
                <h2 class="hero-text-cinematic mb-2 text-center" style="font-size: 1.5rem;">How do you learn best?</h2>
                <p class="text-muted text-center mb-6" style="font-size: 0.8125rem;">Select options that best match your learning habits</p>
                
                <div style="display: flex; flex-direction: column; gap: var(--sp-6); max-height: 280px; overflow-y: auto; padding-right: 8px;">
                    <!-- Q1 -->
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-size: 0.875rem; font-weight: 600;">1. When studying a complex concept, you prefer to:</div>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                            <label class="quiz-option"><input type="radio" name="vark_q1" value="V" required style="display:none;"><div class="quiz-option-marker">A</div> See diagrams, charts, or mind maps</label>
                            <label class="quiz-option"><input type="radio" name="vark_q1" value="A" style="display:none;"><div class="quiz-option-marker">B</div> Hear explanations, podcasts, or discussions</label>
                            <label class="quiz-option"><input type="radio" name="vark_q1" value="R" style="display:none;"><div class="quiz-option-marker">C</div> Read notes, textbooks, or summaries</label>
                            <label class="quiz-option"><input type="radio" name="vark_q1" value="K" style="display:none;"><div class="quiz-option-marker">D</div> Try it hands-on through experiments or practice</label>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-size: 0.875rem; font-weight: 600;">2. Before an exam, you tend to:</div>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                            <label class="quiz-option"><input type="radio" name="vark_q2" value="V" required style="display:none;"><div class="quiz-option-marker">A</div> Review colored notes, highlights, and visuals</label>
                            <label class="quiz-option"><input type="radio" name="vark_q2" value="A" style="display:none;"><div class="quiz-option-marker">B</div> Talk through topics with classmates</label>
                            <label class="quiz-option"><input type="radio" name="vark_q2" value="R" style="display:none;"><div class="quiz-option-marker">C</div> Re-read and rewrite key notes</label>
                            <label class="quiz-option"><input type="radio" name="vark_q2" value="K" style="display:none;"><div class="quiz-option-marker">D</div> Practise past questions and lab exercises</label>
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="font-size: 0.875rem; font-weight: 600;">3. When explaining something new, you understand better with:</div>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                            <label class="quiz-option"><input type="radio" name="vark_q3" value="V" required style="display:none;"><div class="quiz-option-marker">A</div> Blackboard diagrams and schematics</label>
                            <label class="quiz-option"><input type="radio" name="vark_q3" value="A" style="display:none;"><div class="quiz-option-marker">B</div> Clear verbal descriptions and analogies</label>
                            <label class="quiz-option"><input type="radio" name="vark_q3" value="R" style="display:none;"><div class="quiz-option-marker">C</div> Detailed worksheets, lists, and glossaries</label>
                            <label class="quiz-option"><input type="radio" name="vark_q3" value="K" style="display:none;"><div class="quiz-option-marker">D</div> Practical demonstrations and models</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── STEP 4: Learning Pace Preference ── -->
            <div class="onboarding-step">
                <h2 class="hero-text-cinematic mb-2 text-center" style="font-size: 1.5rem;">Choose your default learning pace</h2>
                <p class="text-muted text-center mb-6" style="font-size: 0.8125rem;">This adjusts lesson layout structure dynamically. You can switch this at any time.</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto;">
                    <label class="quiz-option" style="display:flex; flex-direction:column; gap:4px; cursor:pointer;">
                        <input type="radio" name="pace" value="express" required style="display:none;">
                        <div style="font-weight:700; color:var(--clr-primary);">⚡ Express Mode</div>
                        <div class="text-secondary" style="font-size:0.75rem;">Condensed outlines and quick key points. Best for fast assimilation or revision.</div>
                    </label>
                    <label class="quiz-option" style="display:flex; flex-direction:column; gap:4px; cursor:pointer;">
                        <input type="radio" name="pace" value="standard" checked style="display:none;">
                        <div style="font-weight:700; color:var(--clr-info);">📖 Standard Mode</div>
                        <div class="text-secondary" style="font-size:0.75rem;">Balanced course reading layout with sequential explanations and context diagrams.</div>
                    </label>
                    <label class="quiz-option" style="display:flex; flex-direction:column; gap:4px; cursor:pointer;">
                        <input type="radio" name="pace" value="deep" style="display:none;">
                        <div style="font-weight:700; color: #7C3AED;">🔬 Deep-Dive Mode</div>
                        <div class="text-secondary" style="font-size:0.75rem;">Thorough, granular explanations, vocabulary breakdown, and supplementary materials.</div>
                    </label>
                </div>
            </div>

            <!-- ── STEP 5: Summary & Submit ── -->
            <div class="onboarding-step">
                <div style="text-align: center; margin-top: var(--sp-6);">
                    <div style="font-size: 4rem; margin-bottom: var(--sp-4);">🎉</div>
                    <h2 class="hero-text-cinematic mb-4" style="font-size: 1.75rem;">All Setup!</h2>
                    <p class="text-secondary mb-6" style="font-size: 0.875rem;">Your academic dashboard is ready. Here's your personalized settings profile:</p>

                    <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <div style="display: flex; gap: 8px;">
                            <span class="tag-badge" id="summary-vark">VARK: Reading</span>
                            <span class="tag-badge" id="summary-pace">Pace: Standard</span>
                        </div>
                        <div class="text-muted" style="font-size: 0.8125rem;">
                            You can tweak these modes or parameters at any time from your sidebar settings.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div style="display: flex; justify-content: space-between; margin-top: var(--sp-8); gap: 16px;">
                <button type="button" id="back-btn" class="btn btn-outline" onclick="prevStep()">Back</button>
                <button type="button" id="next-btn" class="btn btn-primary" onclick="nextStep()">Continue</button>
                <button type="button" id="finish-btn" class="btn btn-primary animate-pulse-glow" style="display: none;" onclick="submitOnboarding()">Initialize Dashboard</button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 5;

        const steps = document.querySelectorAll('.onboarding-step');
        const dots = document.querySelectorAll('.step-dot');
        const backBtn = document.getElementById('back-btn');
        const nextBtn = document.getElementById('next-btn');
        const finishBtn = document.getElementById('finish-btn');

        // Handle styling selection triggers
        document.querySelectorAll('.quiz-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', () => {
                const parent = radio.parentElement;
                const siblings = parent.parentElement.querySelectorAll('.quiz-option');
                siblings.forEach(el => el.classList.remove('selected'));
                if (radio.checked) {
                    parent.classList.add('selected');
                }
            });
        });

        function updateWho5Score(index, val) {
            document.getElementById(`score-val-${index}`).textContent = `${val}/5`;
            
            // Recompute sum
            let sum = 0;
            document.querySelectorAll('.who5-slider').forEach(el => {
                sum += parseInt(el.value);
            });
            
            const totalEl = document.getElementById('who5-total');
            totalEl.textContent = sum;

            const labelEl = document.getElementById('who5-label');
            labelEl.className = 'badge';
            if (sum <= 12) {
                labelEl.textContent = "We'll be gentle with you today 💛";
                labelEl.classList.add('badge-amber');
            } else if (sum <= 17) {
                labelEl.textContent = "Good balance 😊";
                labelEl.classList.add('badge-blue');
            } else {
                labelEl.textContent = "You're thriving! 🔥";
                labelEl.classList.add('badge-green');
            }
        }

        function updateStepDisplay() {
            steps.forEach((step, index) => {
                step.classList.remove('active');
                if (index === currentStep - 1) {
                    step.classList.add('active');
                }
            });

            dots.forEach((dot, index) => {
                dot.classList.remove('active', 'completed');
                if (index === currentStep - 1) {
                    dot.classList.add('active');
                } else if (index < currentStep - 1) {
                    dot.classList.add('completed');
                }
            });

            // Back button vis
            if (currentStep === 1) {
                backBtn.style.visibility = 'hidden';
            } else {
                backBtn.style.visibility = 'visible';
            }

            // Next / Finish vis
            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                finishBtn.style.display = 'inline-flex';
                updateSummaryBadge();
            } else {
                nextBtn.style.display = 'inline-flex';
                finishBtn.style.display = 'none';
            }
        }

        function nextStep() {
            if (!validateStep(currentStep)) return;
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepDisplay();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
            }
        }

        function validateStep(step) {
            if (step === 3) { // VARK Qs validation
                const radios = document.querySelectorAll('[name^="vark_q"]');
                let valid = true;
                const questions = ['vark_q1', 'vark_q2', 'vark_q3'];
                questions.forEach(q => {
                    const checked = document.querySelector(`input[name="${q}"]:checked`);
                    if (!checked) {
                        valid = false;
                        // Mark error state visual
                        document.querySelector(`input[name="${q}"]`).closest('.onboarding-step').querySelectorAll('.quiz-option').forEach(el => {
                            el.style.borderColor = 'var(--clr-danger)';
                        });
                    }
                });
                return valid;
            }
            return true;
        }

        function calculateVarkResult() {
            // Count selections
            const q1 = document.querySelector('input[name="vark_q1"]:checked')?.value || 'R';
            const q2 = document.querySelector('input[name="vark_q2"]:checked')?.value || 'R';
            const q3 = document.querySelector('input[name="vark_q3"]:checked')?.value || 'R';
            
            const counts = { V: 0, A: 0, R: 0, K: 0 };
            counts[q1]++;
            counts[q2]++;
            counts[q3]++;

            let maxType = 'R';
            let maxCount = -1;
            for (let type in counts) {
                if (counts[type] > maxCount) {
                    maxCount = counts[type];
                    maxType = type;
                }
            }

            const names = { V: 'Visual', A: 'Auditory', R: 'Reading/Writing', K: 'Kinesthetic' };
            return {
                code: maxType.toLowerCase(),
                name: names[maxType]
            };
        }

        function updateSummaryBadge() {
            const vark = calculateVarkResult();
            const pace = document.querySelector('input[name="pace"]:checked')?.value || 'standard';
            
            const varkEl = document.getElementById('summary-vark');
            varkEl.textContent = `VARK Style: ${vark.name}`;
            varkEl.className = `tag-badge tag-vark-${vark.code}`;

            const paceEl = document.getElementById('summary-pace');
            paceEl.textContent = `Pace: ${pace.toUpperCase()}`;
            paceEl.className = `tag-badge tag-pace-${pace}`;
        }

        function submitOnboarding() {
            // Compute final outputs
            let who5Total = 0;
            document.querySelectorAll('.who5-slider').forEach(el => {
                who5Total += parseInt(el.value);
            });

            const vark = calculateVarkResult().code;
            const pace = document.querySelector('input[name="pace"]:checked')?.value || 'standard';

            finishBtn.disabled = true;
            finishBtn.textContent = 'Saving Preferences...';

            fetch('/api/onboarding-save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    who5_score: who5Total,
                    vark_style: vark,
                    current_pace: pace
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Confetti and redirect
                    createConfetti();
                    setTimeout(() => {
                        window.location.href = '/student/dashboard.php';
                    }, 1500);
                } else {
                    alert('Error saving preferences: ' + (data.message || 'unknown error'));
                    finishBtn.disabled = false;
                    finishBtn.textContent = 'Initialize Dashboard';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Network connection error.');
                finishBtn.disabled = false;
                finishBtn.textContent = 'Initialize Dashboard';
            });
        }

        function createConfetti() {
            const colors = ['#D10000', '#FF3B30', '#34C759', '#007AFF', '#FF9500'];
            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti-piece';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.transform = 'scale(' + (Math.random() * 0.8 + 0.2) + ')';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                document.body.appendChild(confetti);
            }
        }
    </script>
</body>
</html>
