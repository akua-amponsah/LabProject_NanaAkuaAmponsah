<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a faculty intern
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../db_connection.php');

if (!isset($_GET['session_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit();
}

try {
    $session_id = $_GET['session_id'];
    $faculty_intern_id = $_SESSION['user_id'];
    
    // Verify that the faculty intern has access to this session
    $stmt = $pdo->prepare("
        SELECT s.course_id
        FROM attendance_sessions s
        INNER JOIN faculty_intern_assignments fia ON s.course_id = fia.course_id
        WHERE s.session_id = ? AND fia.faculty_intern_id = ?
    ");
    $stmt->execute([$session_id, $faculty_intern_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to this session']);
        exit();
    }
    
    // Get all students enrolled in the course with their attendance status for this session
    $stmt = $pdo->prepare("
        SELECT
            u.user_id as student_id,
            CONCAT(u.first_name, ' ', u.last_name) as student_name,
            u.email as student_email,
            a.status
        FROM enrollments e
        INNER JOIN users u ON e.student_id = u.user_id
        INNER JOIN attendance_sessions s ON e.course_id = s.course_id
        LEFT JOIN attendance a ON u.user_id = a.student_id AND a.session_id = ?
        WHERE s.session_id = ? AND e.status = 'approved'
        ORDER BY u.last_name, u.first_name
    ");
    $stmt->execute([$session_id, $session_id]);
    $students = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'students' => $students
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>