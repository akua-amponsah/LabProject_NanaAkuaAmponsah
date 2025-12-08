<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check if faculty intern is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$courseId = intval($_GET['course_id']);
$internId = $_SESSION['user_id'];

try {
    // Verify the faculty intern is assigned to this course
    $stmt = $pdo->prepare("
        SELECT course_id
        FROM course_instructors
        WHERE course_id = ? AND instructor_id = ?
    ");
    $stmt->execute([$courseId, $internId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You are not assigned to this course.']);
        exit();
    }
    
    // Get enrolled students for this course
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