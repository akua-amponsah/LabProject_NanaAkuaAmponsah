<?php
session_start();
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validation
    if (empty($email) || empty($password)) {
        header('Location: ../index.php?error=' . urlencode('Email and password are required.'));
        exit();
    }
    
    try {
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role (3 different dashboards)
            if ($user['role'] === 'student') {
                header('Location: ../dashboard/student-dashboard.php');
            } elseif ($user['role'] === 'faculty') {
                header('Location: ../dashboard/faculty-dashboard.php');
            } else {
                // faculty_intern
                header('Location: ../dashboard/faculty-intern-dashboard.php');
            }
            exit();
            
        } else {
            header('Location: ../index.php?error=' . urlencode('Invalid email or password.'));
            exit();
        }
        
    } catch(PDOException $e) {
        header('Location: ../index.php?error=' . urlencode('Database error occurred.'));
        exit();
    }
    
} else {
    header('Location: ../index.php');
    exit();
}
?>