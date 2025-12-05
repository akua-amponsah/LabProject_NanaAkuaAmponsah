<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

// Check authorization
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $courseCode = trim($_POST['courseCode']);
    $courseName = trim($_POST['courseName']);
    $description = trim($_POST['courseDescription']);
    $createdBy = $_SESSION['user_id'];
    
    // Validation
    if (empty($courseCode) || empty($courseName)) {
        echo json_encode(['success' => false, 'message' => 'Course code and name are required.']);
        exit();
    }
    
    try {
        // Check if course code already exists
        $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_code = ?");
        $stmt->execute([$courseCode]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Course code already exists.']);
            exit();
        }
        
        // Insert course
        $stmt = $pdo->prepare("
            INSERT INTO courses (course_code, course_name, description, created_by) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$courseCode, $courseName, $description, $createdBy]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Course created successfully!',
            'course_id' => $pdo->lastInsertId()
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>