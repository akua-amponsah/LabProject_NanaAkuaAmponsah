<?php
session_start();

// Authorization check - only faculty_intern can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty_intern') {
    header('Location: ../index.php?error=' . urlencode('Unauthorized access. Please login as faculty intern.'));
    exit();
}

$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Intern Dashboard</title>
    <link rel="icon" href="../images/user_icon.png" type="image/png">
    <link rel="stylesheet" href="../css/fi-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<header class="dashboard-header">
    <h1>FACULTY INTERN DASHBOARD</h1>
    <div style="display: flex; align-items: center; gap: 20px;">
        <span style="color: #004b99; font-weight: 600;">Welcome, <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
        <nav class="dashboard-nav">
            <ul>
                <li><a href="#assigned-courses">Assigned Courses</a></li>
                <li><a href="#attendance-sessions">Attendance Sessions</a></li>
                <li><a href="../actions/logout.php" style="color: #d32f2f;">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="dashboard-content">
    <!-- Assigned Courses -->
    <section id="assigned-courses">
        <h2>My Assigned Courses</h2>
        <div id="assignedCoursesList">
            <p style="text-align: center; color: #666;">Loading your assigned courses...</p>
        </div>
    </section>

    <!-- Attendance Sessions -->
    <section id="attendance-sessions">
        <h2>Attendance Sessions</h2>
        <button id="createSessionBtn" style="background-color: #4caf50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600; margin-bottom: 15px;">
            + Create Session
        </button>
        <div id="sessionsList">
            <p style="text-align: center; color: #666;">Loading sessions...</p>
        </div>
    </section>
</main>

<!-- Create Session Modal -->
<div id="createSessionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px;">
        <h2 style="margin-top: 0; color: #0066cc;">Create Attendance Session</h2>
        <form id="createSessionForm">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Select Course</label>
            <select id="sessionCourse" name="course_id" required
                    style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">Loading courses...</option>
            </select>
            
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Session Name</label>
            <input type="text" id="sessionName" name="session_name" placeholder="e.g., Week 1 - Introduction" required
                style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Session Date</label>
            <input type="date" id="sessionDate" name="session_date" required
                style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="cancelSessionBtn" style="padding: 10px 20px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Create Session</button>
            </div>
        </form>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div id="markAttendanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
        <h2 style="margin-top: 0; color: #0066cc;" id="markAttendanceTitle">Mark Attendance</h2>
        <p style="margin-bottom: 20px; color: #666;" id="sessionInfo"></p>
        
        <div id="studentAttendanceList">
            <p style="text-align: center; color: #666;">Loading students...</p>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
            <button id="cancelMarkAttendanceBtn" style="padding: 10px 20px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            <button id="saveAttendanceBtn" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Save Attendance</button>
        </div>
    </div>
</div>

<!-- View Students Modal -->
<div id="viewStudentsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <h2 style="margin-top: 0; color: #0066cc;" id="viewStudentsTitle">Enrolled Students</h2>
        
        <div id="courseStudentsList">
            <p style="text-align: center; color: #666;">Loading students...</p>
        </div>
        
        <button id="closeViewStudentsBtn" style="width: 100%; padding: 10px; background: #ccc; border: none; border-radius: 4px; cursor: pointer; margin-top: 20px;">Close</button>
    </div>
</div>

<script src="../js/faculty-intern-dashboard.js"></script>
</body>
</html>