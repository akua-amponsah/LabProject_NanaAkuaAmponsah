<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check if FACULTY is logged in (NOT faculty_intern, NOT student)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $requestId = intval($_POST['request_id']);
    $action = trim($_POST['action']); // 'approved' or 'rejected'
    $facultyId = $_SESSION['user_id'];
    
    if (empty($requestId) || !in_array($action, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit();
    }
    
    try {
        // Get the enrollment request details
        $stmt = $pdo->prepare("
            SELECT er.*, c.created_by 
            FROM enrollment_requests er
            JOIN courses c ON er.course_id = c.course_id
            WHERE er.request_id = ? AND er.status = 'pending'
        ");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if (!$request) {
            echo json_encode(['success' => false, 'message' => 'Request not found or already processed.']);
            exit();
        }
        
        // Verify faculty owns this course
        if ($request['created_by'] != $facultyId) {
            echo json_encode(['success' => false, 'message' => 'You can only manage requests for your own courses.']);
            exit();
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update request status
        $stmt = $pdo->prepare("UPDATE enrollment_requests SET status = ? WHERE request_id = ?");
        $stmt->execute([$action, $requestId]);
        
        // If approved, add to course_enrollments
        if ($action === 'approved') {
            $stmt = $pdo->prepare("
                INSERT INTO course_enrollments (student_id, course_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$request['student_id'], $request['course_id']]);
        }
        
        $pdo->commit();
        
        $message = $action === 'approved' ? 'Student enrolled successfully!' : 'Request rejected.';
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch(PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>