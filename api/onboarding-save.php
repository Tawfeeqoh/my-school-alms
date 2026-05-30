<?php
// ============================================================
// ALMS — Onboarding Preferences Save Endpoint
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$who5_score = (int)($input['who5_score'] ?? 0);
$vark_style = strtolower(trim($input['vark_style'] ?? 'r'));
$current_pace = strtolower(trim($input['current_pace'] ?? 'standard'));

// Validate
if ($who5_score < 0 || $who5_score > 25) {
    echo json_encode(['success' => false, 'message' => 'Invalid wellness score.']);
    exit;
}
if (!in_array($vark_style, ['v', 'a', 'r', 'k', 'vark'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid VARK profile style.']);
    exit;
}
if (!in_array($current_pace, ['express', 'standard', 'deep'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid pacing preference.']);
    exit;
}

$db = db();

try {
    $stmt = $db->prepare("
        UPDATE student_profiles 
        SET who5_score = ?, vark_style = ?, current_pace = ?, onboarded = 1 
        WHERE user_id = ?
    ");
    $result = $stmt->execute([$who5_score, $vark_style, $current_pace, $_SESSION['user_id']]);

    // Log Activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log = $db->prepare("INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, 'onboarded', ?)");
    $log->execute([$_SESSION['user_id'], $ip]);

    // Also update session cache for UI
    $_SESSION['vark_style'] = $vark_style;
    $_SESSION['current_pace'] = $current_pace;
    $_SESSION['onboarded'] = 1;

    echo json_encode(['success' => true, 'message' => 'Profile onboarding complete.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
