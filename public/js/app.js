// Main JavaScript functionality
class TicketApp {
    constructor() {
        this.init();
    }
    
    init() {
        console.log('Ticket App initialized');
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupAutoHideFlash();
    }
    
    setupEventListeners() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add loading state to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Processing...';
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Submit';
                    }, 3000);
                }
            });
        });
        
        // Store original button text
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.dataset.originalText = btn.innerHTML;
        });
    }
    
    setupFormValidation() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const requiredFields = form.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    field.classList.remove('input-error');
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('input-error');
                        
                        // Create error message if it doesn't exist
                        let errorMessage = field.parentNode.querySelector('.error-message');
                        if (!errorMessage) {
                            errorMessage = document.createElement('span');
                            errorMessage.className = 'error-message';
                            field.parentNode.appendChild(errorMessage);
                        }
                        errorMessage.textContent = 'This field is required.';
                    } else {
                        // Remove error message if exists
                        const errorMessage = field.parentNode.querySelector('.error-message');
                        if (errorMessage) {
                            errorMessage.remove();
                        }
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    this.showToast('Please fill in all required fields.', 'error');
                }
            });
        });
        
        // Real-time validation
        forms.forEach(form => {
            form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('input', () => {
                    field.classList.remove('input-error');
                    const errorMessage = field.parentNode.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });
        });
    }
    
    setupAutoHideFlash() {
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    message.remove();
                }, 500);
            }, 3000);
        });
    }
    
    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `flash-message flash-${type}`;
        toast.textContent = message;
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '1000';
        
        document.body.appendChild(toast);
        
        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 3000);
    }
    
    // Utility function for API calls (if needed later)
    async fetchData(url, options = {}) {
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            this.showToast('Network error occurred', 'error');
            return null;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ticketApp = new TicketApp();
});

// Utility functions
const Utils = {
    formatDate: (dateString) => {
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    },
    
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};