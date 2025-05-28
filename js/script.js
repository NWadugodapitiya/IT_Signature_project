// register.php in

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            const username = document.getElementById('username');
            const mobile = document.getElementById('mobile');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Clear previous error messages
            clearErrors();
            
            // Username validation
            if (!username.value.trim()) {
                showError(username, 'Username is required');
                isValid = false;
            } else if (username.value.trim().length < 3) {
                showError(username, 'Username must be at least 3 characters long');
                isValid = false;
            }
            
            // Mobile validation
            if (!mobile.value.trim()) {
                showError(mobile, 'Mobile number is required');
                isValid = false;
            } else if (!/^[0-9]{10}$/.test(mobile.value.trim())) {
                showError(mobile, 'Please enter a valid 10-digit mobile number');
                isValid = false;
            }
            
            // Password validation
            if (!password.value) {
                showError(password, 'Password is required');
                isValid = false;
            } else if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters long');
                isValid = false;
            }
            
            // Confirm password validation
            if (!confirmPassword.value) {
                showError(confirmPassword, 'Please confirm your password');
                isValid = false;
            } else if (password.value !== confirmPassword.value) {
                showError(confirmPassword, 'Passwords do not match');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = registerForm.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                clearError(this);
            });
            
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }
    
    // Helper functions
    function showError(input, message) {
        const formFloating = input.closest('.form-floating');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.classList.add('is-invalid');
        formFloating.appendChild(errorDiv);
    }
    
    function clearError(input) {
        const formFloating = input.closest('.form-floating');
        const errorDiv = formFloating.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('is-invalid');
    }
    
    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(error => error.remove());
        document.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    }
    
    function validateField(input) {
        clearError(input);
        
        switch(input.id) {
            case 'username':
                if (!input.value.trim()) {
                    showError(input, 'Username is required');
                } else if (input.value.trim().length < 3) {
                    showError(input, 'Username must be at least 3 characters long');
                }
                break;
                
            case 'mobile':
                if (!input.value.trim()) {
                    showError(input, 'Mobile number is required');
                } else if (!/^[0-9]{10}$/.test(input.value.trim())) {
                    showError(input, 'Please enter a valid 10-digit mobile number');
                }
                break;
                
            case 'password':
                if (!input.value) {
                    showError(input, 'Password is required');
                } else if (input.value.length < 6) {
                    showError(input, 'Password must be at least 6 characters long');
                }
                break;
                
            case 'confirm_password':
                const password = document.getElementById('password');
                if (!input.value) {
                    showError(input, 'Please confirm your password');
                } else if (password.value !== input.value) {
                    showError(input, 'Passwords do not match');
                }
                break;
        }
    }
});
// register.php out

// index.html in





// index.html out
