<?php
session_start();

// Authorization check - only faculty can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header('Location: ../index.php?error=' . urlencode('Unauthorized access. Please login as faculty.'));
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
    <title>Faculty Dashboard</title>
    <link rel="icon" href="../images/user_icon.png" type="image/png">
    <link rel="stylesheet" href="../css/faculty-dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<header class="dashboard-header">
    <h1>FACULTY DASHBOARD</h1>
    <div style="display: flex; align-items: center; gap: 20px;">
        <span style="color: #004b99; font-weight: 600;">Welcome, <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
        <nav class="dashboard-nav">
            <ul>
                <li><a href="#my-courses">My Courses</a></li>
                <li><a href="#enrollment-requests">Enrollment Requests</a></li>
                <li><a href="../actions/logout.php" style="color: #d32f2f;">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="dashboard-content">
    <!-- Create Course Section -->
    <section id="create-course">
        <h2>Create New Course</h2>
        <button id="createCourseBtn" style="background-color: #0066cc; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: 600;">
            + Create Course
        </button>
    </section>

    <!-- My Courses -->
    <section id="my-courses">
        <h2>My Courses</h2>
        <div id="myCoursesList">
            <p style="text-align: center; color: #666;">Loading your courses...</p>
        </div>
    </section>

    <!-- Enrollment Requests -->
    <section id="enrollment-requests">
        <h2>Pending Enrollment Requests</h2>
        <div id="enrollmentRequestsList">
            <p style="text-align: center; color: #666;">Loading enrollment requests...</p>
        </div>
    </section>
</main>

<!-- Create Course Modal -->
<div id="createCourseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px;">
        <h2 style="margin-top: 0; color: #0066cc;">Create New Course</h2>
        <form id="createCourseForm">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Course Code</label>
            <input type="text" id="courseCode" name="courseCode" required 
                style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Course Name</label>
            <input type="text" id="courseName" name="courseName" required 
                style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Description</label>
            <textarea id="courseDescription" name="courseDescription" rows="4" 
                    style="width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="cancelCourseBtn" style="padding: 10px 20px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Create Course</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Course Modal -->
<div id="manageCourseModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
        <h2 style="margin-top: 0; color: #0066cc;" id="manageCourseTitle">Manage Course</h2>
        
        <!-- Enrolled Students -->
        <h3>Enrolled Students</h3>
        <div id="enrolledStudentsList" style="margin-bottom: 20px;">
            <p style="color: #666;">Loading students...</p>
        </div>
        
        <!-- Assign Faculty Intern -->
        <h3>Assign Faculty Intern</h3>
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <select id="facultyInternSelect" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">Select Faculty Intern...</option>
            </select>
            <button id="assignInternBtn" style="background: #0066cc; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                Assign
            </button>
        </div>
        
        <!-- Assigned Faculty Interns -->
        <h3>Assigned Faculty Interns</h3>
        <div id="assignedInternsList" style="margin-bottom: 20px;">
            <p style="color: #666;">Loading assigned interns...</p>
        </div>
        
        <button id="closeManageCourseBtn" style="width: 100%; padding: 10px; background: #ccc; border: none; border-radius: 4px; cursor: pointer;">Close</button>
    </div>
</div>

<script src="../js/faculty-dashboard-main.js"></script>
</body>
</html>
