<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header('Location: dashboard/student-dashboard.php');
    } else {
        header('Location: dashboard/faculty-intern-dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN PAGE</title>
    <link rel="icon" href="images/calendar.png" type="image/png">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/validation.js" defer></script>
</head>

<body>
    <div class="main-title">ATTENDANCE RECORD MANAGEMENT SYSTEM</div>
    <form action="actions/login-handler.php" method="POST">
        <div class="login-page">
            <label for="EmailInput">Enter your email:</label>
            <input type="email" name="email" id="EmailInput" placeholder="email" required>
            
            <label for="PasswordInput">Enter your password:</label>
            <input type="password" name="password" id="PasswordInput" placeholder="password" required>

            <input type="submit" value="LOGIN">
        </div>

        <div class="signup-link">
            Don't have an account?
            <a href="signup.php">Sign up here</a>
        </div>
    </form>
</body>
</html>