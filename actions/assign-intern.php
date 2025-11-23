<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $courseId = intval($_POST['course_id']);
    $instructorId = intval($_POST['instructor_id']);
    $facultyId = $_SESSION['user_id'];
    
    try {
        // Verify faculty owns this course
        $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_id = ? AND created_by = ?");
        $stmt->execute([$courseId, $facultyId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access to this course.']);
            exit();
        }
        
        // Verify instructor is actually a faculty_intern
        $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt->execute([$instructorId]);
        $user = $stmt->fetch();
        
        if (!$user || $user['role'] !== 'faculty_intern') {
            echo json_encode(['success' => false, 'message' => 'Selected user is not a faculty intern.']);
            exit();
        }
        
        // Assign intern to course
        $stmt = $pdo->prepare("
            INSERT INTO course_instructors (course_id, instructor_id, assigned_by)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$courseId, $instructorId, $facultyId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Faculty intern assigned successfully!'
        ]);
        
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'This faculty intern is already assigned to this course.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
        }
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>