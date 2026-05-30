<?php
// ============================================================
// ALMS — Common Dashboard Topbar Include
// ============================================================

// Build appropriate greeting
$greeting = 'Welcome';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $greeting .= ' Admin';
        if (isset($_SESSION['last_name'])) {
            $greeting .= ' ' . htmlspecialchars($_SESSION['last_name']);
        }
    } else {
        $title = $_SESSION['title'] ?? ($_SESSION['role'] === 'student' ? 'Student' : 'Lecturer');
        $greeting .= ' ' . htmlspecialchars($title) . ' ' . htmlspecialchars($_SESSION['first_name'] ?? '');
    }
}

// User Initials
$initials = 'U';
if (isset($_SESSION['first_name'], $_SESSION['last_name'])) {
    $initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));
}

// Avatar path check
$avatarUrl = '';
if (isset($_SESSION['avatar_path']) && !empty($_SESSION['avatar_path'])) {
    $avatarUrl = htmlspecialchars($_SESSION['avatar_path']);
}
?>
<header class="topbar">
    <div class="topbar-left">
        <button class="topbar-hamburger" aria-label="Open Navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div>
            <div class="topbar-greeting"><?= $greeting ?></div>
            <div class="topbar-date" data-date="<?= date('Y-m-d H:i:s') ?>" data-date-style="full"></div>
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- Notification Toggle -->
        <button class="topbar-notif" aria-label="Notifications" onclick="window.location.href='/notifications.php'">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="notification-badge" style="display: none;">0</span>
        </button>

        <!-- User Profile Avatar Link -->
        <?php
        $profileLink = '/student/profile.php';
        if (isset($_SESSION['role'])) {
            if ($_SESSION['role'] === 'admin') $profileLink = '/admin/settings.php';
            if ($_SESSION['role'] === 'lecturer') $profileLink = '/lecturer/profile.php';
        }
        ?>
        <a href="<?= $profileLink ?>" class="topbar-avatar">
            <?php if ($avatarUrl): ?>
                <img src="<?= $avatarUrl ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            <?php else: ?>
                <?= $initials ?>
            <?php endif; ?>
        </a>
    </div>
</header>
