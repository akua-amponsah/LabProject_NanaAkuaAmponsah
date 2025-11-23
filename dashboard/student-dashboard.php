<?php
session_start();

// Authorization check - only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php?error=' . urlencode('Unauthorized access. Please login as a student.'));
    exit();
}

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="icon" href="../images/user_icon.png" type="image/png">
    <link rel="stylesheet" href="../css/student-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<header class="dashboard-header">
    <h1>STUDENT DASHBOARD</h1>
    <div style="display: flex; align-items: center; gap: 20px;">
        <span style="color: #004b99; font-weight: 600;">Welcome, <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
        <nav class="dashboard-nav">
            <ul>
                <li><a href="#my-courses">My Courses</a></li>
                <li><a href="#join-course">Join Course</a></li>
                <li><a href="../actions/logout.php" style="color: #d32f2f;">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="dashboard-content">
    <!-- My Enrolled Courses -->
    <section id="my-courses">
        <h2>My Enrolled Courses</h2>
        <div id="enrolledCoursesList">
            <p style="text-align: center; color: #666;">Loading your courses...</p>
        </div>
    </section>

    <!-- Join Course Section -->
    <section id="join-course">
        <h2>Join a Course</h2>
        <div style="margin-bottom: 20px;">
            <input type="text" id="searchCourse" placeholder="Search for courses..." 
            style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px;">
        </div>
        <div id="availableCoursesList">
            <p style="text-align: center; color: #666;">Loading available courses...</p>
        </div>
    </section>

    <!-- Pending Requests -->
    <section id="pending-requests">
        <h2>Pending Enrollment Requests</h2>
        <div id="pendingRequestsList">
            <p style="text-align: center; color: #666;">Loading your requests...</p>
        </div>
    </section>
</main>

<script src="../js/student-dashboard.js"></script>
</body>
</html>