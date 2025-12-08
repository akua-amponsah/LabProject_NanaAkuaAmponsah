<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a faculty intern
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? '';
    $session_name = trim($_POST['session_name'] ?? '');
    $session_date = $_POST['session_date'] ?? '';
    $faculty_intern_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($course_id) || empty($session_name) || empty($session_date)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    try {
        // Verify that the faculty intern is assigned to this course
        $stmt = $pdo->prepare("
            SELECT c.course_id, c.course_name, c.course_code 
            FROM courses c
            INNER JOIN faculty_intern_assignments fia ON c.course_id = fia.course_id
            WHERE c.course_id = ? AND fia.faculty_intern_id = ?
        ");
        $stmt->execute([$course_id, $faculty_intern_id]);
        $course = $stmt->fetch();
        
        if (!$course) {
            echo json_encode(['success' => false, 'message' => 'You are not assigned to this course']);
            exit();
        }
        
        // Generate unique 6-character session code
        $session_code = generateSessionCode($pdo);
        
        // Insert session into database
        $stmt = $pdo->prepare("
            INSERT INTO attendance_sessions (course_id, session_name, session_date, session_code, created_by_intern) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$course_id, $session_name, $session_date, $session_code, $faculty_intern_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Session created successfully!',
            'session_code' => $session_code
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Function to generate unique session code
function generateSessionCode($pdo) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $max_attempts = 10;
    
    for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Check if code already exists
        $stmt = $pdo->prepare("SELECT session_id FROM attendance_sessions WHERE session_code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->rowCount() === 0) {
            return $code;
        }
    }
    
    return strtoupper(substr(md5(microtime()), 0, 6));
}
?>