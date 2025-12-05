<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $assignmentId = intval($_POST['assignment_id']);
    $facultyId = $_SESSION['user_id'];
    
    try {
        // Verify assignment exists and belongs to faculty
        $stmt = $pdo->prepare("
            SELECT ci.assignment_id
            FROM course_instructors ci
            JOIN courses c ON ci.course_id = c.course_id
            WHERE ci.assignment_id = ? AND c.created_by = ?
        ");
        $stmt->execute([$assignmentId, $facultyId]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
            exit();
        }
        
        // Remove assignment
        $stmt = $pdo->prepare("DELETE FROM course_instructors WHERE assignment_id = ?");
        $stmt->execute([$assignmentId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Faculty intern removed successfully!'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>