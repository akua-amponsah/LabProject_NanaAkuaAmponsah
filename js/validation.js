document.addEventListener('DOMContentLoaded', function () {
    
    // Helper functions
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    function isStrongPassword(password) {
        return /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(password);
    }
    function isValidContact(contact) {
        return /^[0-9]{7,15}$/.test(contact);
    }

    // Display PHP error messages on login page
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: decodeURIComponent(error)
        });
    }

    // --- LOGIN FORM VALIDATION ---
    const loginEmail = document.getElementById('EmailInput');
    const loginPassword = document.getElementById('PasswordInput');
    const loginForm = loginEmail && loginPassword && loginEmail.closest('form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let errors = [];
            if (!loginEmail.value.trim()) {
                errors.push('Email is required.');
            } else if (!isValidEmail(loginEmail.value.trim())) {
                errors.push('Please enter a valid email.');
            }
            if (!loginPassword.value.trim()) {
                errors.push('Password is required.');
            }
            if (errors.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Login Error',
                    html: errors.join('<br>')
                });
            }
            // If no errors, form will submit normally
        });
    }

    // --- SIGN UP FORM VALIDATION WITH AJAX ---
    const signUpForm = document.getElementById('signupForm');
    if (signUpForm) {
        const firstName = document.getElementById('firstName');
        const lastName = document.getElementById('lastName');
        const role = document.getElementById('role');
        const email = document.getElementById('email');
        const contact = document.getElementById('contact');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        signUpForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Always prevent default for AJAX
            
            let errors = [];
            
            // Client-side validation
            if (!firstName.value.trim()) errors.push('First name is required.');
            if (!lastName.value.trim()) errors.push('Last name is required.');
            if (!role.value || role.value === "") errors.push('Please select a role.');
            if (!email.value.trim()) errors.push('Email is required.');
            else if (!isValidEmail(email.value.trim())) errors.push('Please enter a valid email.');
            if (!contact.value.trim()) errors.push('Contact is required.');
            else if (!isValidContact(contact.value.trim())) errors.push('Contact number should be 7-15 digits.');
            if (!password.value.trim()) errors.push('Password is required.');
            else if (!isStrongPassword(password.value.trim())) {
                errors.push('Password must be at least 8 characters and contain both letters and numbers.');
            }
            if (!confirmPassword.value.trim()) errors.push('Please confirm your password.');
            else if (password.value !== confirmPassword.value) errors.push('Passwords do not match.');
            
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Sign Up Error',
                    html: errors.join('<br>')
                });
                return;
            }
            
            // If validation passes, submit via AJAX
            const formData = new FormData(signUpForm);
            
            fetch('actions/signup-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        html: data.errors.join('<br>')
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
        
        // Real-time validation feedback
        [firstName, lastName, email, contact, password, confirmPassword].forEach(function(input) {
            input.addEventListener('input', function() {
                if (input.value.trim()) {
                    input.style.borderColor = '#4caf50';
                } else {
                    input.style.borderColor = '#f44336';
                }
            });
        });
        
        role.addEventListener('change', function() {
            role.style.borderColor = role.value ? '#4caf50' : '#f44336';
        });
    }
});