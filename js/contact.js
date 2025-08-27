// Contact form functionality for Beehive Lodge

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
        
        // Form validation
        const inputs = contactForm.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearErrors);
        });
    }
    
    // Handle contact form submission
    function handleContactSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        
        // Validate all fields
        if (!validateForm(form)) {
            showNotification('error', 'Veuillez corriger les erreurs dans le formulaire.');
            return;
        }
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Envoi en cours...';
        button.disabled = true;
        
        // Prepare data for submission
        const contactData = {
            firstName: formData.get('firstName'),
            lastName: formData.get('lastName'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            subject: formData.get('subject'),
            message: formData.get('message'),
            newsletter: formData.get('newsletter') === 'on',
            timestamp: new Date().toISOString()
        };
        
        // Simulate API call (replace with actual backend integration)
        submitToBackend(contactData)
            .then(response => {
                showNotification('success', 'Merci ! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
                form.reset();
                
                // Track successful submission (for analytics)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'form_submit', {
                        'event_category': 'Contact',
                        'event_label': 'Contact Form'
                    });
                }
            })
            .catch(error => {
                console.error('Contact form error:', error);
                showNotification('error', 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer ou nous contacter directement.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }
    
    // Backend submission function
    async function submitToBackend(data) {
        // Option 1: PHP Backend
        if (window.location.hostname !== 'localhost') {
            const response = await fetch('/api/contact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            return response.json();
        }
        
        // Option 2: EmailJS (Frontend solution)
        else if (typeof emailjs !== 'undefined') {
            return emailjs.send(
                'your_service_id', // Replace with your EmailJS service ID
                'your_template_id', // Replace with your EmailJS template ID
                data,
                'your_public_key' // Replace with your EmailJS public key
            );
        }
        
        // Option 3: Simulation for development
        else {
            return new Promise((resolve) => {
                setTimeout(() => {
                    resolve({ success: true, message: 'Message sent successfully' });
                }, 2000);
            });
        }
    }
    
    // Form validation
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField({ target: input })) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Validate individual field
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        const fieldName = field.name;
        
        // Remove existing error states
        field.classList.remove('is-invalid');
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        let isValid = true;
        let errorMessage = '';
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Ce champ est obligatoire.';
        }
        // Email validation
        else if (fieldName === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Veuillez entrer une adresse email valide.';
            }
        }
        // Phone validation
        else if (fieldName === 'phone' && value) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                isValid = false;
                errorMessage = 'Veuillez entrer un numéro de téléphone valide.';
            }
        }
        // Message length validation
        else if (fieldName === 'message' && value.length > 0 && value.length < 10) {
            isValid = false;
            errorMessage = 'Le message doit contenir au moins 10 caractères.';
        }
        
        // Show error if invalid
        if (!isValid) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = errorMessage;
            field.parentNode.appendChild(errorDiv);
        }
        
        return isValid;
    }
    
    // Clear error states
    function clearErrors(e) {
        const field = e.target;
        if (field.classList.contains('is-invalid')) {
            field.classList.remove('is-invalid');
            const existingError = field.parentNode.querySelector('.invalid-feedback');
            if (existingError) {
                existingError.remove();
            }
        }
    }
    
    // Show notification
    function showNotification(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show notification-alert`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert notification
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        }
        
        // Auto remove after 8 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 150);
            }
        }, 8000);
        
        // Scroll to notification
        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', formatPhoneNumber);
    }
    
    function formatPhoneNumber(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.startsWith('33')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+33' + value.substring(1);
        }
        
        e.target.value = value;
    }
});

// EmailJS configuration (if using EmailJS)
// Uncomment and configure if you want to use EmailJS
/*
(function() {
    emailjs.init("your_public_key"); // Replace with your actual public key
})();
*/

// Google Maps integration enhancement
function initMap() {
    // Custom map styling for Beehive Lodge theme
    const mapStyles = [
        {
            "featureType": "all",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "weight": "2.00"
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#9c9c9c"
                }
            ]
        },
        {
            "featureType": "all",
            "elementType": "labels.text",
            "stylers": [
                {
                    "visibility": "on"
                }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "all",
            "stylers": [
                {
                    "color": "#f2f2f2"
                }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [
                {
                    "visibility": "off"
                }
            ]
        },
        {
            "featureType": "road",
            "elementType": "all",
            "stylers": [
                {
                    "saturation": -100
                },
                {
                    "lightness": 45
                }
            ]
        },
        {
            "featureType": "water",
            "elementType": "all",
            "stylers": [
                {
                    "color": "#D4A017"
                },
                {
                    "visibility": "on"
                }
            ]
        }
    ];
    
    // You can add custom Google Maps JavaScript API integration here
    // if you want to replace the iframe with a more interactive map
}
