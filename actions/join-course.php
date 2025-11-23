<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $courseId = intval($_POST['course_id']);
    $studentId = $_SESSION['user_id'];
    
    if (empty($courseId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid course ID.']);
        exit();
    }
    
    try {
        // Check if already enrolled
        $stmt = $pdo->prepare("SELECT enrollment_id FROM course_enrollments WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course.']);
            exit();
        }
        
        // Check if request already exists
        $stmt = $pdo->prepare("SELECT request_id FROM enrollment_requests WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You already have a pending request for this course.']);
            exit();
        }
        
        // Create enrollment request
        $stmt = $pdo->prepare("
            INSERT INTO enrollment_requests (student_id, course_id, status) 
            VALUES (?, ?, 'pending')
        ");
        
        $stmt->execute([$studentId, $courseId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Enrollment request sent successfully! Waiting for faculty approval.'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>