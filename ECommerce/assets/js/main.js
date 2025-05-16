// Main JavaScript for Artizo E-commerce

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Product Quantity Increment/Decrement
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    if (quantityBtns) {
        quantityBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.closest('.quantity-input').querySelector('input');
                const currentValue = parseInt(input.value);
                
                if (this.classList.contains('quantity-increment')) {
                    input.value = currentValue + 1;
                } else if (currentValue > 1) {
                    input.value = currentValue - 1;
                }
                
                // Trigger change event to update any dependent elements
                const event = new Event('change');
                input.dispatchEvent(event);
            });
        });
    }

    // Product Gallery Image Switcher
    const galleryThumbs = document.querySelectorAll('.product-gallery-thumb');
    if (galleryThumbs) {
        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const mainImage = document.querySelector('.product-main-image');
                if (mainImage) {
                    mainImage.src = this.dataset.image;
                    
                    // Update active state
                    galleryThumbs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    }

    // Size Selection
    const sizeOptions = document.querySelectorAll('.size-option');
    if (sizeOptions) {
        sizeOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                sizeOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Update hidden input for form submission
                const sizeInput = document.querySelector('input[name="size"]');
                if (sizeInput) {
                    sizeInput.value = this.dataset.size;
                }
            });
        });
    }

    // Color Selection
    const colorOptions = document.querySelectorAll('.color-option');
    if (colorOptions) {
        colorOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                colorOptions.forEach(opt => opt.classList.remove('active'));
                
                // Add active class to selected option
                this.classList.add('active');
                
                // Update hidden input for form submission
                const colorInput = document.querySelector('input[name="color"]');
                if (colorInput) {
                    colorInput.value = this.dataset.color;
                }
            });
        });
    }

    // Add to Cart Animation
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    if (addToCartBtns) {
        addToCartBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Only animate if not in a form that needs to submit
                if (!this.closest('form') || this.closest('form').getAttribute('data-ajax') === 'true') {
                    e.preventDefault();
                    
                    const cartIcon = document.querySelector('.cart-icon');
                    if (cartIcon) {
                        cartIcon.classList.add('animate__animated', 'animate__rubberBand');
                        
                        setTimeout(() => {
                            cartIcon.classList.remove('animate__animated', 'animate__rubberBand');
                        }, 1000);
                    }
                    
                    // If using AJAX, you would handle the cart addition here
                    if (this.closest('form') && this.closest('form').getAttribute('data-ajax') === 'true') {
                        // AJAX cart addition logic would go here
                    }
                }
            });
        });
    }

    // Mobile Menu Toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });
    }

    // Filter Toggle on Mobile
    const filterToggle = document.querySelector('.filter-toggle');
    const filterSection = document.querySelector('.filter-section');
    
    if (filterToggle && filterSection) {
        filterToggle.addEventListener('click', function() {
            filterSection.classList.toggle('active');
        });
    }

    // Newsletter Form Validation
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (!isValidEmail(email)) {
                showFormError(emailInput, 'Please enter a valid email address');
                return;
            }
            
            // If valid, you would typically submit the form via AJAX here
            // For now, just show a success message
            const formWrapper = this.closest('.newsletter-wrapper');
            if (formWrapper) {
                formWrapper.innerHTML = '<div class="text-center"><h3 class="text-xl font-semibold mb-2">Thank You!</h3><p>You have been subscribed to our newsletter.</p></div>';
            }
        });
    }

    // Helper function to validate email
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    // Helper function to show form errors
    function showFormError(inputElement, message) {
        // Remove any existing error message
        const existingError = inputElement.nextElementSibling;
        if (existingError && existingError.classList.contains('error-message')) {
            existingError.remove();
        }
        
        // Add error class to input
        inputElement.classList.add('error');
        
        // Create and append error message
        const errorDiv = document.createElement('div');
        errorDiv.classList.add('error-message', 'text-red-500', 'text-sm', 'mt-1');
        errorDiv.textContent = message;
        
        inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
        
        // Remove error state when user starts typing again
        inputElement.addEventListener('input', function() {
            this.classList.remove('error');
            const errorMsg = this.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.remove();
            }
        }, { once: true });
    }
}); 