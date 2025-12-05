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
    
    // Get enrolled students
    $stmt = $pdo->prepare("
        SELECT u.user_id,
        CONCAT(u.first_name, ' ', u.last_name) as student_name,
            u.email as student_email
        FROM course_enrollments ce
        JOIN users u ON ce.student_id = u.user_id
        WHERE ce.course_id = ?
        ORDER BY u.first_name, u.last_name
    ");
    
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>