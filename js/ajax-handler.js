document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const email = formData.get('email');
            const password = formData.get('password');
            
            if (!email || !password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in all fields',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            
            Swal.fire({
                title: 'Logging in...',
                text: 'Please wait',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('actions/login-handler.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: data.message || 'Redirecting to dashboard...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
        
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                } else {
        
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: data.message || 'Invalid email or password',
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Could not connect to server. Please try again.',
                });
            });
        });
    }
});