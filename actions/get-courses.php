<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    if ($role === 'student') {
        // Get enrolled courses for student
        $stmt = $pdo->prepare("
            SELECT c.course_id, c.course_code, c.course_name, c.description,
                   CONCAT(u.first_name, ' ', u.last_name) as instructor_name
            FROM course_enrollments ce
            JOIN courses c ON ce.course_id = c.course_id
            JOIN users u ON c.created_by = u.user_id
            WHERE ce.student_id = ?
            ORDER BY c.course_name
        ");
        $stmt->execute([$userId]);
        $enrolledCourses = $stmt->fetchAll();
        
        // Get available courses (not enrolled and no pending request)
        $stmt = $pdo->prepare("
            SELECT c.course_id, c.course_code, c.course_name, c.description,
                   CONCAT(u.first_name, ' ', u.last_name) as instructor_name
            FROM courses c
            JOIN users u ON c.created_by = u.user_id
            WHERE c.course_id NOT IN (
                SELECT course_id FROM course_enrollments WHERE student_id = ?
            )
            AND c.course_id NOT IN (
                SELECT course_id FROM enrollment_requests 
                WHERE student_id = ? AND status = 'pending'
            )
            ORDER BY c.course_name
        ");
        $stmt->execute([$userId, $userId]);
        $availableCourses = $stmt->fetchAll();
        
        // Get pending requests
        $stmt = $pdo->prepare("
            SELECT er.request_id, c.course_code, c.course_name, er.status
            FROM enrollment_requests er
            JOIN courses c ON er.course_id = c.course_id
            WHERE er.student_id = ? AND er.status = 'pending'
            ORDER BY er.request_id DESC
        ");
        $stmt->execute([$userId]);
        $pendingRequests = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'enrolled' => $enrolledCourses,
            'available' => $availableCourses,
            'pending' => $pendingRequests
        ]);
        
    } else {
        // Get courses created by faculty
        $stmt = $pdo->prepare("
            SELECT course_id, course_code, course_name, description
            FROM courses
            WHERE created_by = ?
            ORDER BY course_name
        ");
        $stmt->execute([$userId]);
        $myCourses = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'courses' => $myCourses
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>
