<?php
// ============================================================
// ALMS — Save Lesson Progress / Unlock Modules
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$lesson_id = (int)($input['lesson_id'] ?? 0);

if ($lesson_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid lesson ID.']);
    exit;
}

$db = db();

try {
    // 1. Mark lesson as complete (ignore if already done)
    $stmt = $db->prepare("INSERT IGNORE INTO lesson_progress (student_id, lesson_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $lesson_id]);

    // 2. Fetch current course_id and sequence of this lesson
    $lessonInfo = $db->prepare("SELECT course_id, sequence_order FROM lessons WHERE id = ?");
    $lessonInfo->execute([$lesson_id]);
    $curr = $lessonInfo->fetch();

    $next_lesson_id = null;
    if ($curr) {
        // Find next sequential lesson in the same course
        $nextStmt = $db->prepare("
            SELECT id FROM lessons 
            WHERE course_id = ? AND sequence_order > ? 
            ORDER BY sequence_order ASC LIMIT 1
        ");
        $nextStmt->execute([$curr['course_id'], $curr['sequence_order']]);
        $next = $nextStmt->fetch();
        if ($next) {
            $next_lesson_id = (int)$next['id'];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Lesson marked as completed.',
        'next_lesson_id' => $next_lesson_id
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
