<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../db_connection.php');

try {
    $student_id = $_SESSION['user_id'];
    $course_id = $_GET['course_id'] ?? null;
    
    if ($course_id) {
        // Get attendance for specific course
        $stmt = $pdo->prepare("
            SELECT
                s.session_id,
                s.session_name,
                s.session_date,
                s.session_code,
                c.course_code,
                c.course_name,
                a.status,
                a.marked_at,
                CASE
                    WHEN a.attendance_id IS NOT NULL THEN 1
                    ELSE 0
                END as has_attended
            FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.course_id
            INNER JOIN enrollments e ON c.course_id = e.course_id
            LEFT JOIN attendance a ON s.session_id = a.session_id AND a.student_id = ?
            WHERE e.student_id = ? AND e.status = 'approved' AND c.course_id = ?
            ORDER BY s.session_date DESC, s.created_at DESC
        ");
        $stmt->execute([$student_id, $student_id, $course_id]);
    } else {
        // Get all attendance records across all enrolled courses
        $stmt = $pdo->prepare("
            SELECT 
                s.session_id,
                s.session_name,
                s.session_date,
                s.session_code,
                c.course_code,
                c.course_name,
                a.status,
                a.marked_at,
                CASE 
                    WHEN a.attendance_id IS NOT NULL THEN 1
                    ELSE 0
                END as has_attended
            FROM attendance_sessions s
            INNER JOIN courses c ON s.course_id = c.course_id
            INNER JOIN enrollments e ON c.course_id = e.course_id
            LEFT JOIN attendance a ON s.session_id = a.session_id AND a.student_id = ?
            WHERE e.student_id = ? AND e.status = 'approved'
            ORDER BY s.session_date DESC, s.created_at DESC
        ");
        $stmt->execute([$student_id, $student_id]);
    }
    
    $sessions = $stmt->fetchAll();
    
    // Calculate statistics
    $total_sessions = count($sessions);
    $attended = 0;
    $missed = 0;
    $late = 0;
    
    foreach ($sessions as $session) {
        if ($session['has_attended']) {
            if ($session['status'] === 'present') {
                $attended++;
            } elseif ($session['status'] === 'late') {
                $late++;
            } elseif ($session['status'] === 'absent') {
                $missed++;
            }
        } else {
            $missed++;
        }
    }
    
    $attendance_rate = $total_sessions > 0 ? round(($attended / $total_sessions) * 100, 1) : 0;
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions,
        'statistics' => [
            'total' => $total_sessions,
            'attended' => $attended,
            'missed' => $missed,
            'late' => $late,
            'attendance_rate' => $attendance_rate
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>