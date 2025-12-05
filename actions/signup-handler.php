<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize inputs
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $role = trim($_POST['role']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    // Server-side validation
    $errors = [];
    
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    
    // UPDATED: Now accepts all 3 roles
    if (empty($role) || !in_array($role, ['student', 'faculty', 'faculty_intern'])) {
        $errors[] = 'Please select a valid role.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (empty($contact) || !preg_match('/^[0-9]{7,15}$/', $contact)) {
        $errors[] = 'Contact number should be 7-15 digits.';
    }
    if (empty($password) || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $errors[] = 'Password must be at least 8 characters with letters and numbers.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'errors' => ['Email already exists.']]);
            exit();
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, contact, password_hash, role) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$firstName, $lastName, $email, $contact, $passwordHash, $role]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Redirecting to login...'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]]);
    }
    
} else {
    echo json_encode(['success' => false, 'errors' => ['Invalid request method.']]);
}
?>