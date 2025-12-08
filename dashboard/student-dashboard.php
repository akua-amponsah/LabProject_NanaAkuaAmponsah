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
                <li><a href="#mark-attendance">Mark Attendance</a></li>
                <li><a href="#my-attendance">My Attendance</a></li>
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

    <!-- Mark Attendance Section -->
    <section id="mark-attendance" style="background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #4caf50; margin-top: 0;">Mark Attendance</h2>
        <p style="color: #666; margin-bottom: 20px;">Enter the attendance code provided by your instructor:</p>
        
        <form id="attendanceCodeForm" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <input type="text" 
                   id="attendanceCode" 
                   name="session_code" 
                   placeholder="Enter 6-digit code (e.g., K8J3N5)" 
                   maxlength="6"
                   style="text-transform: uppercase; padding: 14px 20px; font-size: 18px; width: 300px; border: 2px solid #4caf50; border-radius: 6px; font-weight: 600; letter-spacing: 2px;"
                   required>
            <button type="submit" 
                    style="padding: 14px 30px; background: #4caf50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 16px; transition: background 0.3s;">
                ✓ Submit Attendance
            </button>
        </form>
        
        <div id="attendanceResult" style="margin-top: 20px;"></div>
    </section>

    <!-- My Attendance History -->
    <section id="my-attendance" style="background: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="color: #0066cc; margin-top: 0;">My Attendance History</h2>
        
        <!-- Statistics Cards -->
        <div id="attendanceStats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #4caf50 0%, #45a049 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;" id="statAttended">-</div>
                <div style="font-size: 14px; opacity: 0.9;">Sessions Attended</div>
            </div>
            <div style="background: linear-gradient(135deg, #f44336 0%, #e53935 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;" id="statMissed">-</div>
                <div style="font-size: 14px; opacity: 0.9;">Sessions Missed</div>
            </div>
            <div style="background: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;" id="statLate">-</div>
                <div style="font-size: 14px; opacity: 0.9;">Late Arrivals</div>
            </div>
            <div style="background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); padding: 20px; border-radius: 8px; color: white; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;" id="statRate">-</div>
                <div style="font-size: 14px; opacity: 0.9;">Attendance Rate</div>
            </div>
        </div>

        <!-- Filter by Course -->
        <div style="margin-bottom: 20px;">
            <label style="font-weight: 600; display: block; margin-bottom: 8px;">Filter by Course:</label>
            <select id="courseFilter" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; width: 300px;">
                <option value="">All Courses</option>
            </select>
        </div>

        <!-- Attendance Records Table -->
        <div id="attendanceRecordsList">
            <p style="text-align: center; color: #666;">Loading your attendance records...</p>
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