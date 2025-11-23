<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check if faculty is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$facultyId = $_SESSION['user_id'];

try {
    // Get pending enrollment requests for faculty's courses
    $stmt = $pdo->prepare("
        SELECT er.request_id, er.student_id, er.course_id,
            c.course_code, c.course_name,
            CONCAT(u.first_name, ' ', u.last_name) as student_name,
            u.email as student_email
        FROM enrollment_requests er
        JOIN courses c ON er.course_id = c.course_id
        JOIN users u ON er.student_id = u.user_id
        WHERE c.created_by = ? AND er.status = 'pending'
        ORDER BY er.request_id DESC
    ");
    
    $stmt->execute([$facultyId]);
    $requests = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>