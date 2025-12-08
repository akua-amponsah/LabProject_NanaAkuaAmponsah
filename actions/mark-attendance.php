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
    $session_id = $_POST['session_id'] ?? '';
    $attendance_data = $_POST['attendance_data'] ?? '';
    $faculty_intern_id = $_SESSION['user_id'];
    
    // Validate inputs
    if (empty($session_id) || empty($attendance_data)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit();
    }
    
    try {
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
        
        // Decode attendance data
        $attendance = json_decode($attendance_data, true);
        
        if (!is_array($attendance) || empty($attendance)) {
            echo json_encode(['success' => false, 'message' => 'Invalid attendance data']);
            exit();
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        $marked_count = 0;
        foreach ($attendance as $student_id => $status) {
            // Check if attendance record already exists
            $stmt = $pdo->prepare("
                SELECT attendance_id FROM attendance 
                WHERE session_id = ? AND student_id = ?
            ");
            $stmt->execute([$session_id, $student_id]);
            
            if ($stmt->rowCount() > 0) {
                // Update existing record
                $stmt = $pdo->prepare("
                    UPDATE attendance 
                    SET status = ?, marked_at = NOW()
                    WHERE session_id = ? AND student_id = ?
                ");
                $stmt->execute([$status, $session_id, $student_id]);
            } else {
                // Insert new record
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (session_id, student_id, status, marked_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$session_id, $student_id, $status]);
            }
            $marked_count++;
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Attendance marked successfully for {$marked_count} student(s)!"
        ]);
        
    } catch (PDOException $e) {
        // Rollback on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>