<?php
// ============================================================
// ALMS — Notifications Counter API
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$db = db();

try {
    $stmt = $db->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $res = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'count' => (int)($res['unread_count'] ?? 0)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'count' => 0,
        'error' => $e->getMessage()
    ]);
}
