/**
 * Form Validation Module
 * Handles AJAX validation for all forms
 */
class FormValidator {
    constructor(form) {
        this.form = form;
        this.validationRoute = form.dataset.validationRoute;
        this.validateOn = form.dataset.validateOn || 'blur'; // blur, change, submit
        this.fields = {};
        this.errors = {};
        this.isSubmitting = false;
        
        this.init();
    }
    
    init() {
        // Get all form fields
        const fields = this.form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            if (field.name && !field.disabled) {
                this.fields[field.name] = field;
                
                // Add validation event listeners
                const validateOn = field.dataset.validateOn || this.validateOn;
                
                if (validateOn.includes('blur')) {
                    field.addEventListener('blur', () => this.validateField(field));
                }
                
                if (validateOn.includes('change')) {
                    field.addEventListener('change', () => this.validateField(field));
                }
            }
        });
        
        // Intercept form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }
    
    async validateField(field) {
        const fieldName = field.name;
        if (!fieldName) return;
        
        // Clear previous error for this field
        this.clearFieldError(fieldName);
        
        // Get field value
        let value = field.value;
        
        // Handle file inputs
        if (field.type === 'file') {
            value = field.files.length > 0 ? 'file_selected' : '';
        }
        
        // Skip empty optional fields
        if (!field.hasAttribute('required') && !value) {
            return;
        }
        
        // Show loading state
        this.setFieldLoading(fieldName, true);
        
        try {
            const formData = new FormData();
            formData.append(fieldName, value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            
            // Add other form data for context (for dependent validations)
            Object.keys(this.fields).forEach(key => {
                if (key !== fieldName && this.fields[key].value) {
                    if (this.fields[key].type === 'file') {
                        if (this.fields[key].files.length > 0) {
                            formData.append(key, this.fields[key].files[0]);
                        }
                    } else {
                        formData.append(key, this.fields[key].value);
                    }
                }
            });
            
            const response = await fetch(this.validationRoute, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            this.setFieldLoading(fieldName, false);
            
            if (!data.valid && data.errors && data.errors[fieldName]) {
                this.showFieldError(fieldName, data.errors[fieldName][0]);
                return false;
            } else {
                this.clearFieldError(fieldName);
                return true;
            }
        } catch (error) {
            console.error('Validation error:', error);
            this.setFieldLoading(fieldName, false);
            return true; // Don't block on network errors
        }
    }
    
    async validateForm() {
        let isValid = true;
        const fieldsToValidate = [];
        
        // Collect all fields that need validation
        Object.keys(this.fields).forEach(fieldName => {
            const field = this.fields[fieldName];
            if (field.hasAttribute('required') || field.value) {
                fieldsToValidate.push(field);
            }
        });
        
        // Validate all fields
        for (const field of fieldsToValidate) {
            const fieldValid = await this.validateField(field);
            if (!fieldValid) {
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    async handleSubmit(e) {
        if (this.isSubmitting) {
            e.preventDefault();
            return false;
        }
        
        // Prevent default submission
        e.preventDefault();
        
        // Validate entire form
        const isValid = await this.validateForm();
        
        if (!isValid) {
            // Scroll to first error
            const firstError = this.form.querySelector('.field-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
        
        // If valid, submit the form
        this.isSubmitting = true;
        const submitButton = this.form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = submitButton.dataset.loadingText || 'Submitting...';
        }
        
        // Submit form normally
        this.form.submit();
        
        return true;
    }
    
    showFieldError(fieldName, message) {
        const field = this.fields[fieldName];
        if (!field) return;
        
        // Add error class to field
        field.classList.add('is-invalid', 'border-red-500');
        field.classList.remove('border-green-500');
        
        // Find or create error container
        let errorContainer = this.form.querySelector(`[data-field="${fieldName}"].field-error`);
        
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'field-error text-red-600 text-sm mt-1';
            errorContainer.setAttribute('data-field', fieldName);
            
            // Insert after field or in parent container
            const fieldGroup = field.closest('.mb-3, .mb-4, .field-group, .col-md-6, .col-md-4, .col-md-3');
            if (fieldGroup) {
                fieldGroup.appendChild(errorContainer);
            } else {
                field.parentNode.insertBefore(errorContainer, field.nextSibling);
            }
        }
        
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
        
        this.errors[fieldName] = message;
    }
    
    clearFieldError(fieldName) {
        const field = this.fields[fieldName];
        if (field) {
            field.classList.remove('is-invalid', 'border-red-500');
        }
        
        const errorContainer = this.form.querySelector(`[data-field="${fieldName}"].field-error`);
        if (errorContainer) {
            errorContainer.style.display = 'none';
            errorContainer.textContent = '';
        }
        
        delete this.errors[fieldName];
    }
    
    clearAllErrors() {
        Object.keys(this.fields).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });
    }
    
    setFieldLoading(fieldName, loading) {
        const field = this.fields[fieldName];
        if (!field) return;
        
        if (loading) {
            field.classList.add('validating');
            field.style.opacity = '0.7';
        } else {
            field.classList.remove('validating');
            field.style.opacity = '1';
        }
    }
}

// Initialize validation for all forms with data-validate attribute
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        new FormValidator(form);
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}

