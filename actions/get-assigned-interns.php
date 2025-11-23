<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$courseId = intval($_GET['course_id']);
$facultyId = $_SESSION['user_id'];

try {
    // Verify faculty owns this course
    $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_id = ? AND created_by = ?");
    $stmt->execute([$courseId, $facultyId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to this course.']);
        exit();
    }
    
    // Get assigned interns
    $stmt = $pdo->prepare("
        SELECT ci.assignment_id,
            u.user_id,
            CONCAT(u.first_name, ' ', u.last_name) as intern_name,
            u.email as intern_email
        FROM course_instructors ci
        JOIN users u ON ci.instructor_id = u.user_id
        WHERE ci.course_id = ?
        ORDER BY u.first_name, u.last_name
    ");
    
    $stmt->execute([$courseId]);
    $interns = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'interns' => $interns
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>