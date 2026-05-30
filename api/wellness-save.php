<?php
// ============================================================
// ALMS — Student Wellness Metrics Save Endpoint
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$attention = strtolower(trim($input['attention_span'] ?? 'medium'));
$stress = strtolower(trim($input['stress_level'] ?? 'low'));

// Validate
if (!in_array($attention, ['low', 'medium', 'high'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid attention span level.']);
    exit;
}
if (!in_array($stress, ['low', 'medium', 'high'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid stress level.']);
    exit;
}

$db = db();

try {
    $stmt = $db->prepare("
        INSERT INTO student_wellness_logs (user_id, attention_span, stress_level) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $attention, $stress]);

    // Automatically update the pacing selection on profile if stressed
    if ($stress === 'high') {
        $updatePace = $db->prepare("UPDATE student_profiles SET current_pace = 'express' WHERE user_id = ?");
        $updatePace->execute([$_SESSION['user_id']]);
        $_SESSION['current_pace'] = 'express';
    }

    echo json_encode(['success' => true, 'message' => 'Wellness metrics logged successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
