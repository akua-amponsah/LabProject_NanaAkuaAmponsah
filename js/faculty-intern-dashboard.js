document.addEventListener('DOMContentLoaded', function() {
    
    let currentSessionId = null;
    let currentCourseId = null;
    let attendanceData = {}; // Store attendance selections
    
    // Modal elements - Create Session
    const createSessionBtn = document.getElementById('createSessionBtn');
    const createSessionModal = document.getElementById('createSessionModal');
    const cancelSessionBtn = document.getElementById('cancelSessionBtn');
    const createSessionForm = document.getElementById('createSessionForm');
    const sessionCourse = document.getElementById('sessionCourse');
    
    // Modal elements - Mark Attendance
    const markAttendanceModal = document.getElementById('markAttendanceModal');
    const cancelMarkAttendanceBtn = document.getElementById('cancelMarkAttendanceBtn');
    const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
    
    // Modal elements - View Students
    const viewStudentsModal = document.getElementById('viewStudentsModal');
    const closeViewStudentsBtn = document.getElementById('closeViewStudentsBtn');
    
    // Show create session modal
    createSessionBtn.addEventListener('click', function() {
        loadAssignedCoursesForSession();
        createSessionModal.style.display = 'flex';
    });
    
    // Hide create session modal
    cancelSessionBtn.addEventListener('click', function() {
        createSessionModal.style.display = 'none';
        createSessionForm.reset();
    });
    
    createSessionModal.addEventListener('click', function(e) {
        if (e.target === createSessionModal) {
            createSessionModal.style.display = 'none';
            createSessionForm.reset();
        }
    });
    
    // Close mark attendance modal
    cancelMarkAttendanceBtn.addEventListener('click', function() {
        markAttendanceModal.style.display = 'none';
        currentSessionId = null;
        attendanceData = {};
    });
    
    markAttendanceModal.addEventListener('click', function(e) {
        if (e.target === markAttendanceModal) {
            markAttendanceModal.style.display = 'none';
            currentSessionId = null;
            attendanceData = {};
        }
    });
    
    // Close view students modal
    closeViewStudentsBtn.addEventListener('click', function() {
        viewStudentsModal.style.display = 'none';
        currentCourseId = null;
    });
    
    viewStudentsModal.addEventListener('click', function(e) {
        if (e.target === viewStudentsModal) {
            viewStudentsModal.style.display = 'none';
            currentCourseId = null;
        }
    });
    
    // Handle session creation
    createSessionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(createSessionForm);
        
        fetch('../actions/create-session.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Session Created!',
                    html: `<p>${data.message}</p><p style="margin-top: 10px;"><strong>Session Code:</strong> <span style="font-size: 1.5rem; color: #0066cc;">${data.session_code}</span></p>`,
                    confirmButtonText: 'OK'
                }).then(() => {
                    createSessionModal.style.display = 'none';
                    createSessionForm.reset();
                    loadSessions();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong. Please try again.'
            });
            console.error('Error:', error);
        });
    });
    
    // Save attendance
    saveAttendanceBtn.addEventListener('click', function() {
        if (Object.keys(attendanceData).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Attendance Marked',
                text: 'Please mark attendance for at least one student.'
            });
            return;
        }
        
        const formData = new FormData();
        formData.append('session_id', currentSessionId);
        formData.append('attendance_data', JSON.stringify(attendanceData));
        
        fetch('../actions/mark-attendance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    markAttendanceModal.style.display = 'none';
                    currentSessionId = null;
                    attendanceData = {};
                    loadSessions();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong.'
            });
            console.error('Error:', error);
        });
    });
    
    // Load assigned courses
    function loadAssignedCourses() {
        fetch('../actions/get-intern-courses.php')
        .then(response => response.json())
        .then(data => {
            const coursesList = document.getElementById('assignedCoursesList');
            
            if (data.success && data.courses.length > 0) {
                let html = '<div style="display: grid; gap: 15px;">';
                
                data.courses.forEach(course => {
                    html += `
                        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 10px 0; color: #0066cc;">${course.course_code}</h3>
                                    <p style="margin: 5px 0; font-weight: 600; font-size: 1.1rem;">${course.course_name}</p>
                                    <p style="margin: 5px 0; color: #666;">${course.description || 'No description'}</p>
                                    <p style="margin: 5px 0; color: #888; font-size: 0.9rem;">Assigned by: ${course.faculty_name}</p>
                                </div>
                                <button onclick="viewStudents(${course.course_id}, '${course.course_name}')" 
                                        style="background: #0066cc; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                                    View Students
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                coursesList.innerHTML = html;
            } else {
                coursesList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">You haven\'t been assigned to any courses yet. Contact faculty to get assigned.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
            document.getElementById('assignedCoursesList').innerHTML = '<p style="color: red;">Error loading courses.</p>';
        });
    }
    
    // Load courses for session creation dropdown
    function loadAssignedCoursesForSession() {
        fetch('../actions/get-intern-courses.php')
        .then(response => response.json())
        .then(data => {
            sessionCourse.innerHTML = '<option value="">Select a course...</option>';
            
            if (data.success && data.courses.length > 0) {
                data.courses.forEach(course => {
                    sessionCourse.innerHTML += `<option value="${course.course_id}">${course.course_code} - ${course.course_name}</option>`;
                });
            } else {
                sessionCourse.innerHTML = '<option value="">No courses assigned</option>';
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
            sessionCourse.innerHTML = '<option value="">Error loading courses</option>';
        });
    }
    
    // Load sessions
    function loadSessions() {
        fetch('../actions/get-sessions.php')
        .then(response => response.json())
        .then(data => {
            const sessionsList = document.getElementById('sessionsList');
            
            if (data.success && data.sessions.length > 0) {
                let html = '<div style="display: grid; gap: 15px;">';
                
                data.sessions.forEach(session => {
                    html += `
                        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 10px 0; color: #4caf50;">${session.session_name}</h3>
                                    <p style="margin: 5px 0; font-weight: 600;">${session.course_code} - ${session.course_name}</p>
                                    <p style="margin: 5px 0; color: #666;">Date: ${session.session_date}</p>
                                    <p style="margin: 5px 0; color: #0066cc; font-weight: 600;">Session Code: ${session.session_code}</p>
                                    <p style="margin: 5px 0; color: #888; font-size: 0.9rem;">Attendance: ${session.attendance_count || 0} students marked</p>
                                </div>
                                <button onclick="markAttendance(${session.session_id}, '${session.session_name}', '${session.course_code}')" 
                                        style="background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                                    Mark Attendance
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                sessionsList.innerHTML = html;
            } else {
                sessionsList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No sessions created yet. Create a session to start marking attendance.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading sessions:', error);
            document.getElementById('sessionsList').innerHTML = '<p style="color: red;">Error loading sessions.</p>';
        });
    }
    
    // Mark attendance for a session (global function)
    window.markAttendance = function(sessionId, sessionName, courseCode) {
        currentSessionId = sessionId;
        attendanceData = {};
        
        document.getElementById('markAttendanceTitle').textContent = `Mark Attendance: ${sessionName}`;
        document.getElementById('sessionInfo').textContent = `Course: ${courseCode}`;
        
        markAttendanceModal.style.display = 'flex';
        
        // Load students for this session
        fetch(`../actions/get-session-students.php?session_id=${sessionId}`)
        .then(response => response.json())
        .then(data => {
            const studentsList = document.getElementById('studentAttendanceList');
            
            if (data.success && data.students.length > 0) {
                let html = '<div style="display: grid; gap: 10px;">';
                
                data.students.forEach(student => {
                    const currentStatus = student.status || '';
                    html += `
                        <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="margin: 0; font-weight: 600;">${student.student_name}</p>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">${student.student_email}</p>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="setAttendance(${student.student_id}, 'present')" 
                                        id="btn-${student.student_id}-present"
                                        class="attendance-btn ${currentStatus === 'present' ? 'active' : ''}"
                                        style="background: ${currentStatus === 'present' ? '#4caf50' : '#e0e0e0'}; color: ${currentStatus === 'present' ? 'white' : '#666'}; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    Present
                                </button>
                                <button onclick="setAttendance(${student.student_id}, 'absent')" 
                                        id="btn-${student.student_id}-absent"
                                        class="attendance-btn ${currentStatus === 'absent' ? 'active' : ''}"
                                        style="background: ${currentStatus === 'absent' ? '#f44336' : '#e0e0e0'}; color: ${currentStatus === 'absent' ? 'white' : '#666'}; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    Absent
                                </button>
                                <button onclick="setAttendance(${student.student_id}, 'late')" 
                                        id="btn-${student.student_id}-late"
                                        class="attendance-btn ${currentStatus === 'late' ? 'active' : ''}"
                                        style="background: ${currentStatus === 'late' ? '#ff9800' : '#e0e0e0'}; color: ${currentStatus === 'late' ? 'white' : '#666'}; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                    Late
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Pre-populate existing attendance
                    if (currentStatus) {
                        attendanceData[student.student_id] = currentStatus;
                    }
                });
                
                html += '</div>';
                studentsList.innerHTML = html;
            } else {
                studentsList.innerHTML = '<p style="color: #666; font-style: italic;">No students enrolled in this course.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('studentAttendanceList').innerHTML = '<p style="color: red;">Error loading students.</p>';
        });
    };
    
    // Set attendance status (global function)
    window.setAttendance = function(studentId, status) {
        attendanceData[studentId] = status;
        
        // Update button styles
        ['present', 'absent', 'late'].forEach(s => {
            const btn = document.getElementById(`btn-${studentId}-${s}`);
            if (btn) {
                if (s === status) {
                    btn.style.background = s === 'present' ? '#4caf50' : s === 'absent' ? '#f44336' : '#ff9800';
                    btn.style.color = 'white';
                } else {
                    btn.style.background = '#e0e0e0';
                    btn.style.color = '#666';
                }
            }
        });
    };
    
    // View students in a course (global function)
    window.viewStudents = function(courseId, courseName) {
        currentCourseId = courseId;
        document.getElementById('viewStudentsTitle').textContent = `Enrolled Students: ${courseName}`;
        viewStudentsModal.style.display = 'flex';
        
        fetch(`../actions/get-course-students-intern.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            const studentsList = document.getElementById('courseStudentsList');
            
            if (data.success && data.students.length > 0) {
                let html = '<div style="display: grid; gap: 10px;">';
                
                data.students.forEach(student => {
                    html += `
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 4px;">
                            <p style="margin: 0; font-weight: 600;">${student.student_name}</p>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">${student.student_email}</p>
                        </div>
                    `;
                });
                
                html += '</div>';
                studentsList.innerHTML = html;
            } else {
                studentsList.innerHTML = '<p style="color: #666; font-style: italic;">No students enrolled yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('courseStudentsList').innerHTML = '<p style="color: red;">Error loading students.</p>';
        });
    };
    
    // Initial load
    loadAssignedCourses();
    loadSessions();
});