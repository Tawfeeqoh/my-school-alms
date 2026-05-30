<?php
// ============================================================
// ALMS — AI Assistant Chatroom Portal
// ============================================================
$currentPage = 'ai-assistant';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

if ($_SESSION['role'] !== 'student') {
    header('Location: /index.php');
    exit;
}

$db = db();
// 1. Fetch Student Profile
$stmt = $db->prepare("SELECT vark_style, current_pace FROM student_profiles WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

$vark = $profile['vark_style'] ?? 'r';
$pace = $profile['current_pace'] ?? 'standard';

// 2. Fetch Wellness Logs
$wellnessStmt = $db->prepare("SELECT attention_span, stress_level FROM student_wellness_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT 1");
$wellnessStmt->execute([$_SESSION['user_id']]);
$wellness = $wellnessStmt->fetch();

$attention = $wellness['attention_span'] ?? 'medium';
$stress = $wellness['stress_level'] ?? 'low';

// 3. Build Wellness Context Description Banner
$wellnessBanner = '';
if ($stress === 'high') {
    $wellnessBanner = '🧘 High stress detected. I will keep responses concise and structured with a supportive tone.';
} elseif ($attention === 'low') {
    $wellnessBanner = '⚡ Low attention span logged. I will focus on bite-sized bullet points and definitions.';
} else {
    $wellnessBanner = match($pace) {
        'express' => '⚡ Express Mode: Focus is set to summarized outlines and key takeaway facts.',
        'deep' => '🔬 Deep-Dive Mode: Focus is set to comprehensive, detailed explanations and step-by-step proofs.',
        default => '📖 Standard Mode: Focus is set to balanced lecture notes, structural context, and visual analogies.'
    };
}

$pageTitle = 'AI Study Room';
?>

<!-- ── Navigation ── -->
<?php require_once __DIR__ . '/../includes/nav-student.php'; ?>

<!-- ── Main Content Area ── -->
<div class="main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="page-content animate-fade-in" style="display: flex; flex-direction: column; height: calc(100vh - 64px); max-height: calc(100vh - 64px); padding: var(--sp-6);">
        
        <!-- Header Info -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--clr-border-light); padding-bottom: 12px; flex-shrink: 0;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 2px;">AI Study Room</h2>
                <p class="text-secondary" style="font-size: 0.8125rem; margin-bottom: 0;">Powered by your course materials, VARK style, and mental wellness log.</p>
            </div>
            <div>
                <span class="badge badge-green" style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--clr-success); display: block;"></span>
                    Online
                </span>
            </div>
        </div>

        <!-- Wellness Banner Alert -->
        <?php if ($wellnessBanner): ?>
            <div class="flash-msg info" id="wellness-status-banner" style="margin-bottom: 16px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span><?= htmlspecialchars($wellnessBanner) ?></span>
                </div>
                <button type="button" style="background: none; border: none; font-size: 1rem; cursor: pointer; opacity: 0.7; color: inherit;" onclick="document.getElementById('wellness-status-banner').remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Messages Area -->
        <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 12px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 12px;" class="card-flat">
            
            <!-- Dynamic Initial Welcome Message Loaded via Javascript -->

            <!-- Quick Prompts Chips -->
            <div id="quick-prompts-container" style="display: flex; flex-direction: column; gap: 12px; max-width: 440px; margin: 40px auto 0; text-align: center;">
                <p class="text-secondary" style="font-size: 0.875rem;">Here are some quick shortcuts matching your learning style:</p>
                <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                    <button class="btn btn-outline btn-sm quick-prompt-chip" data-prompt="Explain Binary Trees Basics in <?= htmlspecialchars(strtoupper($vark)) ?> style">
                        Explain Binary Trees in my style 👁️
                    </button>
                    <button class="btn btn-outline btn-sm quick-prompt-chip" data-prompt="Help me study CS 201 topics at my <?= htmlspecialchars($pace) ?> pace">
                        Help me study CS 201 at my pace ⚡
                    </button>
                    <button class="btn btn-outline btn-sm quick-prompt-chip" data-prompt="Quiz me on basic organic chemistry topics">
                        Quiz me on organic chemistry 🔬
                    </button>
                </div>
            </div>

            <!-- Typing indicator (hidden by default) -->
            <div id="chat-typing" class="typing-indicator" style="display: none; align-self: flex-start;">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Chat Input Bar -->
        <div style="background: var(--clr-surface); border: 1px solid var(--clr-border-light); border-radius: var(--radius-xl); padding: 8px 12px; display: flex; gap: 12px; align-items: center; flex-shrink: 0;" class="card-elevated">
            <textarea id="chat-input" placeholder="Ask anything about Binary Search Trees, organic chemistry, or livestock diseases..." 
                      style="flex: 1; background: transparent; border: none; outline: none; font-size: 0.9375rem; color: var(--clr-text); resize: none; height: 44px; display: flex; align-items: center; font-family: var(--font-body);" class="scrollbar-hide"></textarea>
            
            <button id="chat-send-btn" class="btn btn-primary btn-sm" style="border-radius: 12px; padding: 10px 14px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z"/>
                </svg>
            </button>
        </div>

    </div>
</div>

<!-- Define global JS variables before importing script -->
<script>
const VARK_STYLE = '<?= htmlspecialchars($vark) ?>';
const PACE_MODE = '<?= htmlspecialchars($pace) ?>';
const ATTENTION = '<?= htmlspecialchars($attention) ?>';
const STRESS = '<?= htmlspecialchars($stress) ?>';
</script>

<?php
$extraJs = '<script src="/assets/js/chat.js"></script>';
require_once __DIR__ . '/../includes/footer.php';
?>
