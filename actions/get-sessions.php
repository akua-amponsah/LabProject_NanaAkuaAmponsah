<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a faculty intern
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once('../db_connection.php');

try {
    $faculty_intern_id = $_SESSION['user_id'];
    
    // Get all sessions for courses assigned to this faculty intern
    $stmt = $pdo->prepare("
        SELECT
            s.session_id,
            s.session_name,
            s.session_date,
            s.session_code,
            c.course_name,
            c.course_code,
            COUNT(DISTINCT a.attendance_id) as attendance_count
        FROM attendance_sessions s
        INNER JOIN courses c ON s.course_id = c.course_id
        INNER JOIN faculty_intern_assignments fia ON c.course_id = fia.course_id
        LEFT JOIN attendance a ON s.session_id = a.session_id
        WHERE fia.faculty_intern_id = ?
        GROUP BY s.session_id, s.session_name, s.session_date, s.session_code, c.course_name, c.course_code
        ORDER BY s.session_date DESC, s.session_id DESC
    ");
    $stmt->execute([$faculty_intern_id]);
    $sessions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>