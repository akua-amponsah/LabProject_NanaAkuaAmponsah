<?php
session_start();
require_once '../db_connection.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validation
    if (empty($email) || empty($password)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required.'
            ]);
        } else {
            header('Location: ../index.php?error=' . urlencode('Email and password are required.'));
        }
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
            
            // Determine redirect based on role
            if ($user['role'] === 'student') {
                $redirect = 'dashboard/student-dashboard.php';
            } elseif ($user['role'] === 'faculty') {
                $redirect = 'dashboard/faculty-dashboard.php';
            } else {
                $redirect = 'dashboard/faculty-intern-dashboard.php';
            }
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'role' => $user['role'],
                    'redirect' => $redirect
                ]);
            } else {
                header('Location: ' . $redirect);
            }
            exit();
            
        } else {
            // Invalid credentials
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password.'
                ]);
            } else {
                header('Location: ../index.php?error=' . urlencode('Invalid email or password.'));
            }
            exit();
        }
        
    } catch(PDOException $e) {
        // Database error
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        } else {
            header('Location: ../index.php?error=' . urlencode('Database error occurred.'));
        }
        exit();
    }
    
} else {
    // Not a POST request
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    } else {
        header('Location: ../index.php');
    }
    exit();
}
?>