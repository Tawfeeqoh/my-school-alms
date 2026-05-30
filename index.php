<?php
// ============================================================
// ALMS — Public Entrance & Authentication
// ============================================================
require_once __DIR__ . '/config.php';

// Redirect to respective dashboards if already authenticated
if (isAuthenticated()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /admin/dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'lecturer') {
        header('Location: /lecturer/dashboard.php');
        exit;
    } else {
        header('Location: /student/dashboard.php');
        exit;
    }
}

$error = $_GET['error'] ?? '';
$msg = $_GET['msg'] ?? '';

$error_messages = [
    'missing_fields' => 'Please fill in all required fields.',
    'invalid_email' => 'Please enter a valid email address.',
    'invalid_credentials' => 'Email or password is incorrect.',
    'account_locked' => 'Account locked. Try again in 15 minutes.',
    'pending_approval' => 'Your account is awaiting admin approval.',
    'account_suspended' => 'Your account has been suspended. Contact administrator.',
    'session_expired' => 'Your session has expired. Please log in again.',
    'forbidden' => 'Access denied. You do not have permission to view that page.',
    'not_found' => 'The requested page was not found.',
    'csrf_failed' => 'Security token verification failed. Please try again.'
];

$success_messages = [
    'logged_out' => 'You have been signed out successfully.',
    'registered' => 'Registration complete! An admin will review and approve your lecturer account shortly.',
    'registered_student' => 'Registration complete! You can now log in.',
    'password_reset' => 'Password reset successful. You can now log in with your new password.'
];

$display_error = $error_messages[$error] ?? '';
$display_success = $success_messages[$msg] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALMS — Federal College of Animal Health & Production Technology, Ibadan</title>
    <meta name="description" content="Advanced Learning Management System incorporating cognitive wellness tools and adaptive learning paths.">
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
</head>
<body class="film-grain">

    <!-- ── Public Header ── -->
    <header class="public-header" style="position: fixed; top: 0; left: 0; width: 100%; height: 72px; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid var(--clr-border-light); display: flex; align-items: center;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <a href="/" style="display: flex; align-items: center; gap: 12px; font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; color: var(--clr-text);">
                <span style="background: var(--clr-primary); color: #FFFFFF; padding: 6px 12px; border-radius: 8px;">ALMS</span>
                FCAH&PT IB
            </a>
            <div style="display: flex; gap: 24px; align-items: center;">
                <a href="#features" class="nav-link hide-mobile">System Features</a>
                <a href="#departments" class="nav-link hide-mobile">Departments</a>
                <a href="/register.php" class="btn btn-outline btn-sm">Register Portal</a>
            </div>
        </div>
    </header>

    <!-- ── Split Hero Layout ── -->
    <main style="display: flex; flex-wrap: wrap; min-height: 100vh; padding-top: 72px;">
        
        <!-- Left: Cinematic Scrolling Film (58%) -->
        <section class="hide-mobile" style="flex: 0 0 58%; max-width: 58%; height: calc(100vh - 72px); sticky: top 72px; background: #000000; overflow: hidden; position: sticky; top: 72px;">
            <div id="hero-film-container" style="width: 100%; height: 100%;"></div>
        </section>

        <!-- Right: Auth Login Interface (42% desktop, 100% mobile) -->
        <section style="flex: 1 1 42%; max-width: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: var(--sp-12) var(--sp-6); background: var(--clr-surface); min-height: calc(100vh - 72px);">
            <div style="width: 100%; max-width: 400px;">
                <div style="margin-bottom: var(--sp-8); text-align: center;">
                    <h1 class="hero-text-cinematic" style="font-size: 2.25rem; margin-bottom: var(--sp-2);">Welcome Back</h1>
                    <p class="text-secondary" style="font-size: 0.9375rem;">Access your academic command center</p>
                </div>

                <?php if ($display_error): ?>
                    <div class="flash-msg error">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span><?= htmlspecialchars($display_error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($display_success): ?>
                    <div class="flash-msg success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($display_success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="/auth.php?action=login" method="POST" style="display: flex; flex-direction: column; gap: var(--sp-4);">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    
                    <div class="input-group">
                        <label for="login-email">Academic Email</label>
                        <input type="email" id="login-email" name="email" required placeholder="your.name@fcahptib.edu.ng" class="input-field">
                    </div>

                    <div class="input-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label for="login-password">Password</label>
                            <a href="/forgot-password.php" style="font-size: 0.8125rem;">Forgot Password?</a>
                        </div>
                        <div class="input-password-wrap">
                            <input type="password" id="login-password" name="password" required class="input-field">
                            <button type="button" class="toggle-pw" aria-label="Toggle Password Visibility">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="login-remember" name="remember" style="accent-color: var(--clr-primary);">
                        <label for="login-remember" style="font-size: 0.875rem; color: var(--clr-text-secondary); cursor: pointer; user-select: none;">Remember me for 30 days</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" style="margin-top: var(--sp-2);">Sign In</button>
                </form>

                <div style="margin-top: var(--sp-6); text-align: center; font-size: 0.875rem; color: var(--clr-text-secondary);">
                    New to ALMS? <a href="/register.php" style="font-weight: 600;">Register here</a>
                </div>

                <div style="margin-top: var(--sp-8); border-top: 1px solid var(--clr-border-light); padding-top: var(--sp-4); text-align: center;">
                    <a href="/admin/login.php" style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--clr-text-muted);">ADMINISTRATOR SECURE ENTRANCE</a>
                </div>
            </div>
        </section>
    </main>

    <!-- ── System Statistics ── -->
    <section id="stats" style="background: var(--clr-surface); py: var(--sp-16); border-top: 1px solid var(--clr-border-light); border-bottom: 1px solid var(--clr-border-light); padding: 80px 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: center;">
                <div>
                    <div style="font-size: 3rem; font-family: var(--font-heading); font-weight: 800; color: var(--clr-primary);">11</div>
                    <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--clr-text-secondary); margin-top: 8px;">Academic Depts</div>
                </div>
                <div>
                    <div style="font-size: 3rem; font-family: var(--font-heading); font-weight: 800; color: var(--clr-primary);">50+</div>
                    <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--clr-text-secondary); margin-top: 8px;">Approved Lecturers</div>
                </div>
                <div>
                    <div style="font-size: 3rem; font-family: var(--font-heading); font-weight: 800; color: var(--clr-primary);">2,000+</div>
                    <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--clr-text-secondary); margin-top: 8px;">Active Students</div>
                </div>
                <div>
                    <div style="font-size: 3rem; font-family: var(--font-heading); font-weight: 800; color: var(--clr-primary);">100%</div>
                    <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--clr-text-secondary); margin-top: 8px;">Digital Learning</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── System Features ── -->
    <section id="features" style="padding: 80px 0;">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 60px;">
                <h2 style="font-size: 2.25rem; margin-bottom: 12px;" class="hero-text-cinematic">Designed for Diverse Minds</h2>
                <p class="text-secondary">An academic hub that adapts to your learning style, tracks your cognitive health, and keeps you moving forward.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">Adaptive Lesson Player</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">Switch seamlessly between Express Mode, Standard Mode, and Deep Dive depending on your assimilation speed and availability.</p>
                </div>
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">VARK Personalization</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">Interactive content tailored specifically to Visual, Auditory, Read/Write, or Kinesthetic learning preferences.</p>
                </div>
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">RAG Study Assistant</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">An integrated chatbot that uses your lecture notes to answer questions and aligns content with your cognitive load.</p>
                </div>
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">Wellness Support</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">A lightweight pulse check that helps balance cognitive loads during study hours. (Note: Not a medical diagnostic tool).</p>
                </div>
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">Attendance Tracker</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">Keep tabs on your daily presence, leaves of absence, and progress indexes on a visual monthly calendar.</p>
                </div>
                <div class="card-elevated">
                    <h3 style="font-size: 1.25rem; margin-bottom: 12px;">Secure Academic Registrar</h3>
                    <p class="text-secondary" style="font-size: 0.875rem;">Exclusive access for registered students and verified staff members, authorized by institutional rosters.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── Academic Departments ── -->
    <section id="departments" style="background: var(--clr-surface); padding: 80px 0; border-top: 1px solid var(--clr-border-light);">
        <div class="container">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 60px;">
                <h2 style="font-size: 2.25rem; margin-bottom: 12px;" class="hero-text-cinematic">11 Fields of Academic Excellence</h2>
                <p class="text-secondary">Fully functional departments mapping ND and HND curricula at FCAH&PT Ibadan.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                <?php
                $depts = [
                    'Animal Health & Production Technology',
                    'Agricultural Technology',
                    'Fisheries Technology',
                    'Science Laboratory Technology',
                    'Computer Science',
                    'Home & Rural Economics',
                    'Statistics',
                    'Wildlife & Ecotourism Management',
                    'Veterinary Technology',
                    'Pasture & Range Management',
                    'Agribusiness Management'
                ];
                foreach ($depts as $d):
                ?>
                <div style="padding: 20px; border-radius: 12px; background: var(--clr-bg); border: 1px solid var(--clr-border-light); font-weight: 600; font-size: 0.9375rem; text-align: center;">
                    <?= htmlspecialchars($d) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ── Public Footer ── -->
    <footer style="background: #000000; color: #FFFFFF; padding: 60px 0; font-size: 0.875rem;">
        <div class="container" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px;">
            <div>
                <a href="/" style="font-family: var(--font-heading); font-weight: 800; font-size: 1.5rem; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
                    <span style="background: var(--clr-primary); padding: 4px 10px; border-radius: 6px;">ALMS</span>
                </a>
                <p style="margin-top: 12px; color: var(--clr-text-muted); max-width: 280px;">Federal College of Animal Health & Production Technology, Ibadan, Nigeria.</p>
            </div>
            <div>
                <h4 style="color: #FFFFFF; font-size: 1rem; margin-bottom: 16px;">Resources</h4>
                <ul style="display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="#features" style="color: var(--clr-text-muted);">Features</a></li>
                    <li><a href="#departments" style="color: var(--clr-text-muted);">Departments</a></li>
                    <li><a href="/register.php" style="color: var(--clr-text-muted);">Student Registration</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: #FFFFFF; font-size: 1rem; margin-bottom: 16px;">Security</h4>
                <ul style="display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="/admin/login.php" style="color: var(--clr-text-muted);">Admin Portal</a></li>
                    <li><a href="/forgot-password.php" style="color: var(--clr-text-muted);">Reset Password</a></li>
                </ul>
            </div>
        </div>
        <div class="container" style="margin-top: 40px; border-top: 1px solid #222; padding-top: 20px; text-align: center; color: var(--clr-text-muted); font-size: 0.75rem;">
            &copy; <?= date('Y') ?> Advanced Learning Management System (ALMS). All rights reserved. Built for FCAH&PT Ibadan.
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/herofilm.js"></script>
</body>
</html>
