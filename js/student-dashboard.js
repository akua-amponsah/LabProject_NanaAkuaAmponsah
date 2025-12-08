document.addEventListener('DOMContentLoaded', function() {
    
    const searchInput = document.getElementById('searchCourse');
    const courseFilter = document.getElementById('courseFilter');
    let allAvailableCourses = [];
    let enrolledCourses = [];
    
    // Handle attendance code submission
    const attendanceCodeForm = document.getElementById('attendanceCodeForm');
    attendanceCodeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const code = formData.get('session_code');
        
        // Show loading state
        const resultDiv = document.getElementById('attendanceResult');
        resultDiv.innerHTML = `
            <div style="padding: 15px; background: #e3f2fd; border: 1px solid #90caf9; border-radius: 4px; color: #1565c0;">
                <strong>⏳ Submitting attendance...</strong>
            </div>
        `;
        
        fetch('../actions/submit-attendance-code.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success message
                resultDiv.innerHTML = `
                    <div style="padding: 20px; background: #d4edda; border: 2px solid #28a745; border-radius: 6px; color: #155724;">
                        <h3 style="margin: 0 0 10px 0; color: #28a745;">✓ ${data.message}</h3>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #c3e6cb;">
                            <p style="margin: 5px 0;"><strong>Session:</strong> ${data.session_name}</p>
                            <p style="margin: 5px 0;"><strong>Course:</strong> ${data.course_name}</p>
                            <p style="margin: 5px 0;"><strong>Date:</strong> ${data.session_date}</p>
                        </div>
                    </div>
                `;
                
                // Show SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Attendance Marked!',
                    html: `
                        <p><strong>Session:</strong> ${data.session_name}</p>
                        <p><strong>Course:</strong> ${data.course_name}</p>
                        <p><strong>Date:</strong> ${data.session_date}</p>
                    `,
                    confirmButtonColor: '#4caf50'
                });
                
                // Clear form
                attendanceCodeForm.reset();
                
                // Reload attendance records
                loadAttendanceRecords();
                
                // Auto-hide success message after 10 seconds
                setTimeout(() => {
                    resultDiv.innerHTML = '';
                }, 10000);
                
            } else {
                // Error message
                resultDiv.innerHTML = `
                    <div style="padding: 15px; background: #f8d7da; border: 2px solid #f44336; border-radius: 6px; color: #721c24;">
                        <strong>✗ ${data.message}</strong>
                    </div>
                `;
                
                // Show SweetAlert for error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonColor: '#f44336'
                });
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div style="padding: 15px; background: #f8d7da; border: 2px solid #f44336; border-radius: 6px; color: #721c24;">
                    <strong>✗ Error submitting attendance. Please try again.</strong>
                </div>
            `;
            console.error('Error:', error);
        });
    });
    
    // Load attendance records
    function loadAttendanceRecords(courseId = '') {
        const url = courseId ? `../actions/get-student-attendance.php?course_id=${courseId}` : '../actions/get-student-attendance.php';
        
        fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update statistics
                document.getElementById('statAttended').textContent = data.statistics.attended;
                document.getElementById('statMissed').textContent = data.statistics.missed;
                document.getElementById('statLate').textContent = data.statistics.late;
                document.getElementById('statRate').textContent = data.statistics.attendance_rate + '%';
                
                // Display attendance records
                displayAttendanceRecords(data.sessions);
            }
        })
        .catch(error => {
            console.error('Error loading attendance records:', error);
            document.getElementById('attendanceRecordsList').innerHTML = '<p style="color: red;">Error loading attendance records.</p>';
        });
    }
    
    // Display attendance records
    function displayAttendanceRecords(sessions) {
        const recordsList = document.getElementById('attendanceRecordsList');
        
        if (sessions.length > 0) {
            let html = '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse; background: white;">';
            html += `
                <thead>
                    <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Date</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Course</th>
                        <th style="padding: 12px; text-align: left; font-weight: 600;">Session</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                        <th style="padding: 12px; text-align: center; font-weight: 600;">Marked At</th>
                    </tr>
                </thead>
                <tbody>
            `;
            
            sessions.forEach(session => {
                let statusBadge = '';
                if (session.has_attended == 1) {
                    if (session.status === 'present') {
                        statusBadge = '<span style="background: #4caf50; color: white; padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">✓ Present</span>';
                    } else if (session.status === 'late') {
                        statusBadge = '<span style="background: #ff9800; color: white; padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">⏰ Late</span>';
                    } else if (session.status === 'absent') {
                        statusBadge = '<span style="background: #f44336; color: white; padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">✗ Absent</span>';
                    }
                } else {
                    statusBadge = '<span style="background: #9e9e9e; color: white; padding: 5px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">— Not Marked</span>';
                }
                
                const markedAt = session.marked_at ? new Date(session.marked_at).toLocaleString() : '—';
                
                html += `
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">${session.session_date}</td>
                        <td style="padding: 12px;">${session.course_code}</td>
                        <td style="padding: 12px;">${session.session_name}</td>
                        <td style="padding: 12px; text-align: center;">${statusBadge}</td>
                        <td style="padding: 12px; text-align: center; color: #666; font-size: 0.9rem;">${markedAt}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            recordsList.innerHTML = html;
        } else {
            recordsList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No attendance records found.</p>';
        }
    }
    
    // Course filter change event
    courseFilter.addEventListener('change', function() {
        loadAttendanceRecords(this.value);
    });
    
    // Load enrolled courses
    function loadEnrolledCourses() {
        fetch('../actions/get-courses.php')
        .then(response => response.json())
        .then(data => {
            const enrolledList = document.getElementById('enrolledCoursesList');
            
            if (data.success && data.enrolled.length > 0) {
                enrolledCourses = data.enrolled;
                
                // Populate course filter dropdown
                let filterOptions = '<option value="">All Courses</option>';
                data.enrolled.forEach(course => {
                    filterOptions += `<option value="${course.course_id}">${course.course_code} - ${course.course_name}</option>`;
                });
                courseFilter.innerHTML = filterOptions;
                
                // Display enrolled courses
                let html = '<div style="display: grid; gap: 15px;">';
                
                data.enrolled.forEach(course => {
                    html += `
                        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                            <h3 style="margin: 0 0 10px 0; color: #0066cc;">${course.course_code}</h3>
                            <p style="margin: 5px 0; font-weight: 600; font-size: 1.1rem;">${course.course_name}</p>
                            <p style="margin: 5px 0; color: #666;">${course.description || 'No description'}</p>
                            <p style="margin: 5px 0; color: #888; font-size: 0.9rem;">Instructor: ${course.instructor_name}</p>
                        </div>
                    `;
                });
                
                html += '</div>';
                enrolledList.innerHTML = html;
            } else {
                enrolledList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">You are not enrolled in any courses yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading enrolled courses:', error);
            document.getElementById('enrolledCoursesList').innerHTML = '<p style="color: red;">Error loading courses.</p>';
        });
    }
    
    // Load available courses
    function loadAvailableCourses() {
        fetch('../actions/get-courses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allAvailableCourses = data.available;
                displayAvailableCourses(allAvailableCourses);
            }
        })
        .catch(error => {
            console.error('Error loading available courses:', error);
            document.getElementById('availableCoursesList').innerHTML = '<p style="color: red;">Error loading courses.</p>';
        });
    }
    
    // Display available courses
    function displayAvailableCourses(courses) {
        const availableList = document.getElementById('availableCoursesList');
        
        if (courses.length > 0) {
            let html = '<div style="display: grid; gap: 15px;">';
            
            courses.forEach(course => {
                html += `
                    <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 10px 0; color: #0066cc;">${course.course_code}</h3>
                            <p style="margin: 5px 0; font-weight: 600; font-size: 1.1rem;">${course.course_name}</p>
                            <p style="margin: 5px 0; color: #666;">${course.description || 'No description'}</p>
                            <p style="margin: 5px 0; color: #888; font-size: 0.9rem;">Instructor: ${course.instructor_name}</p>
                        </div>
                        <button onclick="joinCourse(${course.course_id})" 
                                style="background: #0066cc; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                            Join Course
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            availableList.innerHTML = html;
        } else {
            availableList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No courses available to join.</p>';
        }
    }
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredCourses = allAvailableCourses.filter(course => 
            course.course_code.toLowerCase().includes(searchTerm) ||
            course.course_name.toLowerCase().includes(searchTerm) ||
            (course.description && course.description.toLowerCase().includes(searchTerm))
        );
        displayAvailableCourses(filteredCourses);
    });
    
    // Join course function (global)
    window.joinCourse = function(courseId) {
        const formData = new FormData();
        formData.append('course_id', courseId);
        
        fetch('../actions/join-course.php', {
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
                    loadAvailableCourses(); // Refresh available courses
                    loadPendingRequests(); // Refresh pending requests
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
    };
    
    // Load pending requests
    function loadPendingRequests() {
        fetch('../actions/get-courses.php')
        .then(response => response.json())
        .then(data => {
            const pendingList = document.getElementById('pendingRequestsList');
            
            if (data.success && data.pending.length > 0) {
                let html = '<div style="display: grid; gap: 15px;">';
                
                data.pending.forEach(request => {
                    html += `
                        <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="margin: 0; font-weight: 600;">${request.course_code} - ${request.course_name}</p>
                            </div>
                            <span style="background: #ff9800; color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">
                                Pending
                            </span>
                        </div>
                    `;
                });
                
                html += '</div>';
                pendingList.innerHTML = html;
            } else {
                pendingList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No pending requests.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading pending requests:', error);
            document.getElementById('pendingRequestsList').innerHTML = '<p style="color: red;">Error loading requests.</p>';
        });
    }
    
    // Initial load
    loadEnrolledCourses();
    loadAvailableCourses();
    loadPendingRequests();
    loadAttendanceRecords();
});
