document.addEventListener('DOMContentLoaded', function() {
    
    let currentCourseId = null; // Track which course is being managed
    
    // Modal elements - Create Course
    const createCourseBtn = document.getElementById('createCourseBtn');
    const createCourseModal = document.getElementById('createCourseModal');
    const cancelCourseBtn = document.getElementById('cancelCourseBtn');
    const createCourseForm = document.getElementById('createCourseForm');
    
    // Modal elements - Manage Course
    const manageCourseModal = document.getElementById('manageCourseModal');
    const closeManageCourseBtn = document.getElementById('closeManageCourseBtn');
    const assignInternBtn = document.getElementById('assignInternBtn');
    const facultyInternSelect = document.getElementById('facultyInternSelect');
    
    // Show create course modal
    createCourseBtn.addEventListener('click', function() {
        createCourseModal.style.display = 'flex';
    });
    
    // Hide create course modal
    cancelCourseBtn.addEventListener('click', function() {
        createCourseModal.style.display = 'none';
        createCourseForm.reset();
    });
    
    // Close modal when clicking outside
    createCourseModal.addEventListener('click', function(e) {
        if (e.target === createCourseModal) {
            createCourseModal.style.display = 'none';
            createCourseForm.reset();
        }
    });
    
    // Close manage course modal
    closeManageCourseBtn.addEventListener('click', function() {
        manageCourseModal.style.display = 'none';
        currentCourseId = null;
    });
    
    manageCourseModal.addEventListener('click', function(e) {
        if (e.target === manageCourseModal) {
            manageCourseModal.style.display = 'none';
            currentCourseId = null;
        }
    });
    
    // Handle course creation
    createCourseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(createCourseForm);
        
        fetch('../actions/create-course.php', {
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
                    createCourseModal.style.display = 'none';
                    createCourseForm.reset();
                    loadMyCourses(); // Refresh course list
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
    
    // Load faculty courses
    function loadMyCourses() {
        fetch('../actions/get-courses.php')
        .then(response => response.json())
        .then(data => {
            const coursesList = document.getElementById('myCoursesList');
            
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
                                </div>
                                <button onclick="manageCourse(${course.course_id}, '${course.course_name}')" 
                                        style="background: #0066cc; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                                    Manage
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                coursesList.innerHTML = html;
            } else {
                coursesList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">You haven\'t created any courses yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
            document.getElementById('myCoursesList').innerHTML = '<p style="color: red;">Error loading courses.</p>';
        });
    }
    
    // Load enrollment requests
    function loadEnrollmentRequests() {
        fetch('../actions/get-enrollment-requests.php')
        .then(response => response.json())
        .then(data => {
            const requestsList = document.getElementById('enrollmentRequestsList');
            
            if (data.success && data.requests.length > 0) {
                let html = '<div style="display: grid; gap: 15px;">';
                
                data.requests.forEach(request => {
                    html += `
                        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <p style="margin: 0; font-weight: 600; font-size: 1.1rem;">${request.student_name}</p>
                                    <p style="margin: 5px 0; color: #666;">${request.student_email}</p>
                                    <p style="margin: 5px 0; color: #0066cc; font-weight: 600;">${request.course_code} - ${request.course_name}</p>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="handleRequest(${request.request_id}, 'approved')" 
                                            style="background: #4caf50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        Approve
                                    </button>
                                    <button onclick="handleRequest(${request.request_id}, 'rejected')" 
                                            style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                requestsList.innerHTML = html;
            } else {
                requestsList.innerHTML = '<p style="text-align: center; color: #666; font-style: italic;">No pending enrollment requests.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            document.getElementById('enrollmentRequestsList').innerHTML = '<p style="color: red;">Error loading requests.</p>';
        });
    }
    
    // Handle request approval/rejection (global function)
    window.handleRequest = function(requestId, action) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', action);
        
        fetch('../actions/manage-requests.php', {
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
                    loadEnrollmentRequests(); // Refresh list
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
    
    // Manage course - show modal with students and interns (global function)
    window.manageCourse = function(courseId, courseName) {
        currentCourseId = courseId;
        document.getElementById('manageCourseTitle').textContent = `Manage: ${courseName}`;
        manageCourseModal.style.display = 'flex';
        
        // Load enrolled students
        loadEnrolledStudents(courseId);
        
        // Load faculty interns for assignment
        loadFacultyInterns();
        
        // Load assigned faculty interns
        loadAssignedInterns(courseId);
    };
    
    // Load enrolled students for a course
    function loadEnrolledStudents(courseId) {
        fetch(`../actions/get-course-students.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            const studentsList = document.getElementById('enrolledStudentsList');
            
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
            document.getElementById('enrolledStudentsList').innerHTML = '<p style="color: red;">Error loading students.</p>';
        });
    }
    
    // Load faculty interns for dropdown
    function loadFacultyInterns() {
        fetch('../actions/get-faculty-interns.php')
        .then(response => response.json())
        .then(data => {
            const select = facultyInternSelect;
            select.innerHTML = '<option value="">Select Faculty Intern...</option>';
            
            if (data.success && data.interns.length > 0) {
                data.interns.forEach(intern => {
                    select.innerHTML += `<option value="${intern.user_id}">${intern.name} (${intern.email})</option>`;
                });
            }
        })
        .catch(error => {
            console.error('Error loading faculty interns:', error);
        });
    }
    
    // Load assigned faculty interns for a course
    function loadAssignedInterns(courseId) {
        fetch(`../actions/get-assigned-interns.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            const internsList = document.getElementById('assignedInternsList');
            
            if (data.success && data.interns.length > 0) {
                let html = '<div style="display: grid; gap: 10px;">';
                
                data.interns.forEach(intern => {
                    html += `
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="margin: 0; font-weight: 600;">${intern.intern_name}</p>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">${intern.intern_email}</p>
                            </div>
                            <button onclick="removeIntern(${intern.assignment_id})" 
                                    style="background: #f44336; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                                Remove
                            </button>
                        </div>
                    `;
                });
                
                html += '</div>';
                internsList.innerHTML = html;
            } else {
                internsList.innerHTML = '<p style="color: #666; font-style: italic;">No faculty interns assigned yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading assigned interns:', error);
            document.getElementById('assignedInternsList').innerHTML = '<p style="color: red;">Error loading interns.</p>';
        });
    }
    
    // Assign faculty intern to course
    assignInternBtn.addEventListener('click', function() {
        const internId = facultyInternSelect.value;
        
        if (!internId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select a faculty intern to assign.'
            });
            return;
        }
        
        const formData = new FormData();
        formData.append('course_id', currentCourseId);
        formData.append('instructor_id', internId);
        
        fetch('../actions/assign-intern.php', {
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
                    facultyInternSelect.value = '';
                    loadAssignedInterns(currentCourseId);
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
    
    // Remove faculty intern from course (global function)
    window.removeIntern = function(assignmentId) {
        Swal.fire({
            title: 'Remove Faculty Intern?',
            text: 'This will remove the faculty intern from this course.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            cancelButtonColor: '#ccc',
            confirmButtonText: 'Yes, remove'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('assignment_id', assignmentId);
                
                fetch('../actions/remove-intern.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            loadAssignedInterns(currentCourseId);
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
            }
        });
    };
    
    // Initial load
    loadMyCourses();
    loadEnrollmentRequests();
});