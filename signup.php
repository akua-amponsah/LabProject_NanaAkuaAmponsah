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
    <title>SIGN UP PAGE</title>
    <link rel="icon" href="images/calendar.png" type="image/png">
    <link rel="stylesheet" href="css/signup.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/validation.js" defer></script>
</head>

<body>
<div class="main-title">SIGN UP</div>
<form action="actions/signup-handler.php" method="POST" id="signupForm">
    <div class="SignUpPage">
        <label for="firstName">First Name</label>
        <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>

        <label for="lastName">Last Name</label>
        <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="" disabled selected>Select your role</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="faculty_intern">Faculty Intern</option>
        </select>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email address" required>

        <label for="contact">Contact</label>
        <input type="tel" id="contact" name="contact" placeholder="Enter your contact" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>

        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>

        <button type="submit" class="submit-btn">SUBMIT</button>
        
        <div style="text-align: center; margin-top: 15px;">
            Already have an account? <a href="index.php" style="color: #0066cc;">Login here</a>
        </div>
    </div>
</form>

</body>
</html>