<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

try {
    // Get all faculty interns
    $stmt = $pdo->prepare("
        SELECT user_id,
            CONCAT(first_name, ' ', last_name) as name,
            email
        FROM users
        WHERE role = 'faculty_intern'
        ORDER BY first_name, last_name
    ");
    
    $stmt->execute();
    $interns = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'interns' => $interns
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
}
?>