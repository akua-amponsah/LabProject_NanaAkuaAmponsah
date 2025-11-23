document.addEventListener('DOMContentLoaded', function() {
    
    const searchInput = document.getElementById('searchCourse');
    let allAvailableCourses = [];
    
    // Load enrolled courses
    function loadEnrolledCourses() {
        fetch('../actions/get-courses.php')
        .then(response => response.json())
        .then(data => {
            const enrolledList = document.getElementById('enrolledCoursesList');
            
            if (data.success && data.enrolled.length > 0) {
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
});