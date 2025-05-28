// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset previous error states
            clearErrors();
            
            let isValid = true;
            const inputs = form.querySelectorAll('input[required]');
            
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });
            
            // Additional validation for registration form
            if (form.querySelector('#confirmPasswordInput')) {
                const password = form.querySelector('#passwordInput').value;
                const confirmPassword = form.querySelector('#confirmPasswordInput').value;
                
                if (password !== confirmPassword) {
                    showError('#confirmPasswordInput', 'Passwords do not match');
                    isValid = false;
                }
                
                // Check terms checkbox
                const termsCheck = form.querySelector('#termsCheck');
                if (!termsCheck.checked) {
                    showError('#termsCheck', 'You must agree to the Terms of Service');
                    isValid = false;
                }
            }
            
            if (isValid) {
                // Show success message
                showSuccess(form);
            }
        });
    });
    
    // Add floating label animation
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
            if (!input.value) {
                input.parentElement.classList.remove('focused');
            }
        });
    });
});

function validateInput(input) {
    const value = input.value.trim();
    
    if (!value) {
        showError(input, 'This field is required');
        return false;
    }
    
    if (input.type === 'email' && !isValidEmail(value)) {
        showError(input, 'Please enter a valid email address');
        return false;
    }
    
    if (input.type === 'password' && value.length < 8) {
        showError(input, 'Password must be at least 8 characters long');
        return false;
    }
    
    return true;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(input, message) {
    const inputElement = input.id ? document.querySelector(`#${input.id}`) : document.querySelector(input);
    const formFloating = inputElement.closest('.form-floating, .form-check');
    
    // Remove any existing error message
    const existingError = formFloating.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error classes
    inputElement.classList.add('is-invalid');
    
    // Create and append error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    formFloating.appendChild(errorDiv);
}

function clearErrors() {
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.remove();
    });
}

function showSuccess(form) {
    // Clear the form
    form.reset();
    
    // Create success alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-success mt-3 animate__animated animate__fadeIn';
    alert.role = 'alert';
    
    // Set success message based on form type
    const isLoginForm = !form.querySelector('#confirmPasswordInput');
    alert.textContent = isLoginForm ? 
        'Login successful! Redirecting...' : 
        'Registration successful! Please check your email to verify your account.';
    
    // Insert alert before the form
    form.parentElement.insertBefore(alert, form);
    
    // Remove alert after 3 seconds
    setTimeout(() => {
        alert.remove();
    }, 3000);
} 