<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_code = strtoupper(trim($_POST['session_code'] ?? ''));
    $student_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($session_code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter an attendance code']);
        exit();
    }
    
    try {
        // Find the session by code
        $stmt = $pdo->prepare("
            SELECT s.session_id, s.session_name, s.session_date, s.course_id,
                c.course_code, c.course_name
            FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.course_id
            WHERE s.session_code = ? AND s.is_active = 1
        ");
        $stmt->execute([$session_code]);
        $session = $stmt->fetch();
        
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Invalid attendance code']);
            exit();
        }
        
        // Check if student is enrolled in this course
        $stmt = $pdo->prepare("
            SELECT enrollment_id
            FROM enrollments
            WHERE student_id = ? AND course_id = ? AND status = 'approved'
        ");
        $stmt->execute([$student_id, $session['course_id']]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
            exit();
        }
        
        // Check if attendance already marked
        $stmt = $pdo->prepare("
            SELECT attendance_id, status 
            FROM attendance 
            WHERE session_id = ? AND student_id = ?
        ");
        $stmt->execute([$session['session_id'], $student_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Attendance already marked
            $statusText = ucfirst($existing['status']);
            echo json_encode([
                'success' => false, 
                'message' => "Attendance already marked as {$statusText} for this session"
            ]);
            exit();
        }
        
        // Mark attendance as present
        $stmt = $pdo->prepare("
            INSERT INTO attendance (session_id, student_id, status, marked_at)
            VALUES (?, ?, 'present', NOW())
        ");
        $stmt->execute([$session['session_id'], $student_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Attendance marked successfully!',
            'session_name' => $session['session_name'],
            'course_name' => $session['course_code'] . ' - ' . $session['course_name'],
            'session_date' => $session['session_date']
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>