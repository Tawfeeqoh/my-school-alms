<?php
// ============================================================
// ALMS — Update Student Pacing Endpoint
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$pace = strtolower(trim($input['pace'] ?? 'standard'));

// Validate
if (!in_array($pace, ['express', 'standard', 'deep'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid pace. Options: express, standard, deep.']);
    exit;
}

$db = db();

try {
    $stmt = $db->prepare("UPDATE student_profiles SET current_pace = ? WHERE user_id = ?");
    $stmt->execute([$pace, $_SESSION['user_id']]);

    $_SESSION['current_pace'] = $pace;

    echo json_encode(['success' => true, 'message' => 'Study pacing updated successfully.', 'pace' => $pace]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
