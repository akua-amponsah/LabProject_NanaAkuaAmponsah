<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check if faculty intern is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$internId = $_SESSION['user_id'];

try {
    // Get courses assigned to this faculty intern
    $stmt = $pdo->prepare("
        SELECT c.course_id, c.course_code, c.course_name, c.description,
            CONCAT(u.first_name, ' ', u.last_name) as faculty_name,
            u.email as faculty_email
        FROM course_instructors ci
        JOIN courses c ON ci.course_id = c.course_id
        JOIN users u ON c.created_by = u.user_id
        WHERE ci.instructor_id = ?
        ORDER BY c.course_name
    ");
    
    $stmt->execute([$internId]);
    $courses = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>
