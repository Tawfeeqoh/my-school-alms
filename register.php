<?php
// ============================================================
// ALMS — Portal Registration (Multi-Step)
// ============================================================
require_once __DIR__ . '/config.php';

if (isAuthenticated()) {
    header('Location: /index.php');
    exit;
}

// Fetch departments for select fields
$db = db();
$depts = [];
try {
    $stmt = $db->query("SELECT id, name FROM departments ORDER BY name ASC");
    $depts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback if DB query fails during migration setup
    $depts = [
        ['id' => 1, 'name' => 'Animal Health'],
        ['id' => 2, 'name' => 'Agricultural Extension'],
        ['id' => 3, 'name' => 'Fisheries'],
        ['id' => 4, 'name' => 'Science Laboratory Technology'],
        ['id' => 5, 'name' => 'Computer Science'],
        ['id' => 6, 'name' => 'Animal Production'],
        ['id' => 7, 'name' => 'Statistics'],
        ['id' => 8, 'name' => 'Physics'],
        ['id' => 9, 'name' => 'Veterinary'],
        ['id' => 10, 'name' => 'SWD'],
        ['id' => 11, 'name' => 'NCC'],
    ];
}

$error = $_GET['error'] ?? '';
$error_msg = '';
if ($error === 'matric_not_authorized') {
    $error_msg = 'Your matriculation number is not registered on the authorized student list.';
} elseif ($error === 'email_exists') {
    $error_msg = 'An account with this email address already exists.';
} elseif ($error === 'matric_exists') {
    $error_msg = 'An account with this matriculation number already exists.';
} elseif ($error === 'password_mismatch') {
    $error_msg = 'Passwords do not match.';
} elseif ($error === 'db_error') {
    $error_msg = 'Database insertion failed. Please try again.';
} elseif ($error === 'invalid_fields') {
    $error_msg = 'One or more fields contain invalid data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — ALMS</title>
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <style>
        .step-container { display: none; }
        .step-container.active { display: block; animation: fadeInUp 0.4s var(--ease-out); }
        .role-card {
            border: 2px solid var(--clr-border-light);
            cursor: pointer;
            transition: all var(--duration-normal) var(--ease-out);
        }
        .role-card:hover, .role-card.selected {
            border-color: var(--clr-primary);
            background: var(--clr-primary-light);
            transform: translateY(-4px);
        }
    </style>
</head>
<body class="film-grain" style="background: var(--clr-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: var(--sp-6);">
    <canvas id="synapse-canvas" style="position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.55;"></canvas>

    <div class="glass-card-strong w-full" style="max-width: 540px;">
        <div style="text-align: center; margin-bottom: var(--sp-6);">
            <a href="/" style="font-family: var(--font-heading); font-weight: 800; font-size: 1.5rem; color: var(--clr-text); display: inline-flex; align-items: center; gap: 8px;">
                <span style="background: var(--clr-primary); color: #FFFFFF; padding: 4px 8px; border-radius: 6px; font-size: 1.125rem;">ALMS</span>
                Academic Registrar
            </a>
            <p class="text-secondary" style="font-size: 0.875rem; margin-top: var(--sp-2);">Federal College of Animal Health & Production Technology, Ibadan</p>
        </div>

        <?php if ($error_msg): ?>
            <div class="flash-msg error">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>

        <!-- Step Indicator -->
        <div id="step-indicator-wrapper" style="display: none; margin-bottom: var(--sp-6);">
            <div class="step-dots">
                <div class="step-dot active"></div>
                <div class="step-dot"></div>
                <div class="step-dot"></div>
            </div>
        </div>

        <!-- ── STEP 0: Role Selection ── -->
        <div id="role-selection" class="step-container active">
            <h2 class="text-center hero-text-cinematic mb-6" style="font-size: 1.5rem;">Select Your Academic Profile</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: var(--sp-6);">
                <div class="card-elevated role-card text-center" onclick="selectRole('student')">
                    <div style="font-size: 2.5rem; margin-bottom: var(--sp-2);">🎓</div>
                    <h3 style="font-size: 1.125rem; margin-bottom: 8px;">Student</h3>
                    <p class="text-secondary" style="font-size: 0.75rem;">Access courses, AI tutor, and track academic wellness.</p>
                </div>
                <div class="card-elevated role-card text-center" onclick="selectRole('lecturer')">
                    <div style="font-size: 2.5rem; margin-bottom: var(--sp-2);">🔬</div>
                    <h3 style="font-size: 1.125rem; margin-bottom: 8px;">Lecturer</h3>
                    <p class="text-secondary" style="font-size: 0.75rem;">Build courses, grade assessments, and view classroom health trends.</p>
                </div>
            </div>
            <div style="text-align: center;">
                <p class="text-secondary style="font-size: 0.875rem;">Already have an account? <a href="/index.php" style="font-weight: 600;">Sign in</a></p>
            </div>
        </div>

        <!-- ── MAIN FORM ── -->
        <form id="registration-form" action="/auth.php?action=register" method="POST" style="display: none;">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="role" id="role-input">

            <!-- Student Forms -->
            <div id="student-steps">
                <!-- Student Step 1: Personal Info -->
                <div class="student-step step-container">
                    <h3 class="mb-4">Personal Details</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--sp-4);">
                        <div class="input-group">
                            <label for="s-first-name">First Name</label>
                            <input type="text" id="s-first-name" name="first_name" placeholder="Hamzat" class="input-field">
                        </div>
                        <div class="input-group">
                            <label for="s-last-name">Last Name</label>
                            <input type="text" id="s-last-name" name="last_name" placeholder="Olasupo" class="input-field">
                        </div>
                        <div class="input-group">
                            <label for="s-email">Email Address</label>
                            <input type="email" id="s-email" name="email" placeholder="hamzat@fcahptib.edu.ng" class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Student Step 2: Academic Info -->
                <div class="student-step step-container">
                    <h3 class="mb-4">Academic Registry</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--sp-4);">
                        <div class="input-group">
                            <label for="s-matric">Matriculation Number</label>
                            <input type="text" id="s-matric" name="matric_number" placeholder="FCA/2026/0001" class="input-field">
                            <span class="input-hint">Must match institutional authorized registration rolls.</span>
                        </div>
                        <div class="input-group">
                            <label for="s-dept">Department</label>
                            <select id="s-dept" name="department_id" class="input-field">
                                <option value="">Select Department</option>
                                <?php foreach ($depts as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="s-level">Study Level</label>
                            <select id="s-level" name="level_id" class="input-field">
                                <option value="">Select Level</option>
                                <option value="1">ND I</option>
                                <option value="2">ND II</option>
                                <option value="3">HND I</option>
                                <option value="4">HND II</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lecturer Forms -->
            <div id="lecturer-steps">
                <!-- Lecturer Step 1: Personal Info -->
                <div class="lecturer-step step-container">
                    <h3 class="mb-4">Personal Details</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--sp-4);">
                        <div class="input-group">
                            <label for="l-first-name">First Name</label>
                            <input type="text" id="l-first-name" name="first_name" placeholder="Timothy" class="input-field">
                        </div>
                        <div class="input-group">
                            <label for="l-last-name">Last Name</label>
                            <input type="text" id="l-last-name" name="last_name" placeholder="Adegoke" class="input-field">
                        </div>
                        <div class="input-group">
                            <label for="l-email">Email</label>
                            <input type="email" id="l-email" name="email" placeholder="t.adegoke@gmail.com" class="input-field">
                        </div>
                    </div>
                </div>

                <!-- Lecturer Step 2: Staff Profile -->
                <div class="lecturer-step step-container">
                    <h3 class="mb-4">Staff Details</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--sp-4);">
                        <div class="input-group">
                            <label for="l-title">Academic Title</label>
                            <select id="l-title" name="title" class="input-field">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Engineer">Engineer</option>
                                <option value="Prof.">Prof.</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="l-dept">Primary Department</label>
                            <select id="l-dept" name="department_id" class="input-field">
                                <option value="">Select Primary Department</option>
                                <?php foreach ($depts as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shared Step 3: Password Configuration -->
            <div id="shared-password-step" class="step-container">
                <h3 class="mb-4">Security Credentials</h3>
                <div style="display: flex; flex-direction: column; gap: var(--sp-4);">
                    <div class="input-group">
                        <label for="pw">Password</label>
                        <div class="input-password-wrap">
                            <input type="password" id="pw" name="password" required class="input-field">
                            <button type="button" class="toggle-pw" aria-label="Toggle Password Visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <div class="strength-meter" id="pw-strength">
                            <div class="strength-meter-bar"></div>
                            <div class="strength-meter-bar"></div>
                            <div class="strength-meter-bar"></div>
                            <div class="strength-meter-bar"></div>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="pw-confirm">Confirm Password</label>
                        <div class="input-password-wrap">
                            <input type="password" id="pw-confirm" name="confirm_password" required class="input-field">
                            <button type="button" class="toggle-pw" aria-label="Toggle Password Visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div style="display: flex; justify-content: space-between; margin-top: var(--sp-8); gap: 16px;">
                <button type="button" id="back-btn" class="btn btn-outline" onclick="prevStep()">Back</button>
                <button type="button" id="next-btn" class="btn btn-primary" onclick="nextStep()">Continue</button>
                <button type="submit" id="submit-btn" class="btn btn-primary" style="display: none;">Register Account</button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
    <script>
        let currentRole = '';
        let currentStep = 0;
        const totalSteps = 3;

        const roleSelection = document.getElementById('role-selection');
        const form = document.getElementById('registration-form');
        const roleInput = document.getElementById('role-input');
        const stepIndicator = document.getElementById('step-indicator-wrapper');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        const dots = document.querySelectorAll('.step-dot');

        function selectRole(role) {
            currentRole = role;
            roleInput.value = role;
            roleSelection.classList.remove('active');
            roleSelection.style.display = 'none';
            form.style.display = 'block';
            stepIndicator.style.display = 'block';
            
            // Activate first step
            currentStep = 1;
            updateStepDisplay();
        }

        function updateStepDisplay() {
            // Hide all steps
            document.querySelectorAll('.step-container').forEach(el => el.classList.remove('active'));
            
            // Show current step based on role
            let targetStep;
            if (currentStep === 3) {
                targetStep = document.getElementById('shared-password-step');
            } else {
                const selector = `.${currentRole}-step`;
                targetStep = document.querySelectorAll(selector)[currentStep - 1];
            }

            if (targetStep) {
                targetStep.classList.add('active');
            }

            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.remove('active', 'completed');
                if (index === currentStep - 1) {
                    dot.classList.add('active');
                } else if (index < currentStep - 1) {
                    dot.classList.add('completed');
                }
            });

            // Update button visibility
            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-flex';
            } else {
                nextBtn.style.display = 'inline-flex';
                submitBtn.style.display = 'none';
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
            } else {
                // Return to selection
                form.style.display = 'none';
                stepIndicator.style.display = 'none';
                roleSelection.style.display = 'block';
                roleSelection.classList.add('active');
            }
        }

        function validateStep(step) {
            let inputs = [];
            if (step === 3) {
                inputs = document.getElementById('shared-password-step').querySelectorAll('input[required]');
            } else {
                const selector = `.${currentRole}-step`;
                const activeContainer = document.querySelectorAll(selector)[step - 1];
                inputs = activeContainer.querySelectorAll('input, select');
            }

            let valid = true;
            inputs.forEach(input => {
                input.classList.remove('error');
                if (input.hasAttribute('required') || step < 3) {
                    if (!input.value.trim()) {
                        input.classList.add('error');
                        valid = false;
                    }
                }
            });
            
            // Additional email check
            if (step === 1) {
                const emailEl = document.getElementById(`${currentRole === 'student' ? 's' : 'l'}-email`);
                if (emailEl && emailEl.value) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(emailEl.value)) {
                        emailEl.classList.add('error');
                        valid = false;
                    }
                }
            }

            return valid;
        }

        // Live password strength feedback
        const pwField = document.getElementById('pw');
        const strengthMeter = document.getElementById('pw-strength');
        if (pwField && strengthMeter) {
            pwField.addEventListener('input', () => {
                const val = pwField.value;
                let strength = '';
                if (val.length >= 8) {
                    let matches = 0;
                    if (/[a-z]/.test(val)) matches++;
                    if (/[A-Z]/.test(val)) matches++;
                    if (/[0-9]/.test(val)) matches++;
                    if (/[^A-Za-z0-9]/.test(val)) matches++;

                    if (matches === 1) strength = 'weak';
                    else if (matches === 2) strength = 'fair';
                    else if (matches === 3) strength = 'good';
                    else if (matches === 4) strength = 'strong';
                } else if (val.length > 0) {
                    strength = 'weak';
                }
                strengthMeter.setAttribute('data-strength', strength);
            });
        }

        // ── Organic Connected Synapses Background Canvas ──
        const synapseCanvas = document.getElementById('synapse-canvas');
        if (synapseCanvas) {
            const ctx = synapseCanvas.getContext('2d');
            let nodes = [];
            const nodeCount = 45;
            const maxDistance = 110;
            let mouseX = -1000;
            let mouseY = -1000;

            function resizeSynapseCanvas() {
                synapseCanvas.width = window.innerWidth;
                synapseCanvas.height = window.innerHeight;
            }
            resizeSynapseCanvas();
            window.addEventListener('resize', resizeSynapseCanvas);

            class SynapseNode {
                constructor() {
                    this.x = Math.random() * synapseCanvas.width;
                    this.y = Math.random() * synapseCanvas.height;
                    this.vx = (Math.random() * 0.6 - 0.3);
                    this.vy = (Math.random() * 0.6 - 0.3);
                    this.radius = Math.random() * 2 + 1.2;
                }
                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0 || this.x > synapseCanvas.width) this.vx *= -1;
                    if (this.y < 0 || this.y > synapseCanvas.height) this.vy *= -1;

                    // Subtle mouse pull
                    const dx = mouseX - this.x;
                    const dy = mouseY - this.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 150) {
                        this.x += dx * 0.008;
                        this.y += dy * 0.008;
                    }
                }
                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(209, 0, 0, 0.3)';
                    ctx.fill();
                }
            }

            for (let i = 0; i < nodeCount; i++) {
                nodes.push(new SynapseNode());
            }

            window.addEventListener('mousemove', (e) => {
                mouseX = e.clientX;
                mouseY = e.clientY;
            });
            window.addEventListener('mouseleave', () => {
                mouseX = -1000;
                mouseY = -1000;
            });

            function animateSynapse() {
                ctx.clearRect(0, 0, synapseCanvas.width, synapseCanvas.height);
                
                for (let i = 0; i < nodes.length; i++) {
                    nodes[i].update();
                    nodes[i].draw();

                    for (let j = i + 1; j < nodes.length; j++) {
                        const dx = nodes[i].x - nodes[j].x;
                        const dy = nodes[i].y - nodes[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < maxDistance) {
                            const opacity = (1 - (dist / maxDistance)) * 0.22;
                            ctx.beginPath();
                            ctx.moveTo(nodes[i].x, nodes[i].y);
                            ctx.lineTo(nodes[j].x, nodes[j].y);
                            ctx.strokeStyle = `rgba(209, 0, 0, ${opacity})`;
                            ctx.lineWidth = 0.8;
                            ctx.stroke();
                        }
                    }
                }
                requestAnimationFrame(animateSynapse);
            }
            animateSynapse();
        }
    </script>
</body>
</html>
