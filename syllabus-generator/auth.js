// Authentication and subscription management
const API_URL = 'https://app.getgradegenie.com/new'; // Production server endpoint

// User state
let currentUser = null;

// Check if user is logged in on page load
document.addEventListener('DOMContentLoaded', function() {
    checkAuthStatus();
});

// Check authentication status
async function checkAuthStatus() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        showAuthModal();
        return false;
    }

    try {
        const response = await fetch(`${API_URL}/api/syllabus-verify-token.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.ok) {
            const userData = await response.json();
            currentUser = userData.user;
            
            // Check if trial is ending soon (less than 24 hours)
            if (userData.user.trial_ending === 1) {
                showTrialEndingNotification();
            }
            
            // Check if trial has ended
            if (userData.user.trial_end_date && new Date(userData.user.trial_end_date) < new Date() && 
                userData.user.subscription_status !== 'active') {
                showSubscriptionRequiredModal();
                return false;
            }
            
            return true;
        } else {
            localStorage.removeItem('auth_token');
            showAuthModal();
            return false;
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        localStorage.removeItem('auth_token');
        showAuthModal();
        return false;
    }
}

// Register new user
async function registerUser(email, password, name) {
    try {
        const response = await fetch(`${API_URL}/api/register-with-trial.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password, name })
        });

        const data = await response.json();
        
        if (response.ok) {
            localStorage.setItem('auth_token', data.token);
            currentUser = data.user;
            hideAuthModal();
            showTrialStartedModal();
            return true;
        } else {
            showError(data.message || 'Registration failed');
            return false;
        }
    } catch (error) {
        console.error('Registration failed:', error);
        showError('Registration failed. Please try again.');
        return false;
    }
}

// Login user
async function loginUser(email, password) {
    try {
        const response = await fetch(`${API_URL}/api/syllabus-login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (response.ok) {
            localStorage.setItem('auth_token', data.token);
            currentUser = data.user;
            hideAuthModal();
            
            // Check if trial has ended
            if (data.user.trial_end_date && new Date(data.user.trial_end_date) < new Date() && 
                data.user.subscription_status !== 'active') {
                showSubscriptionRequiredModal();
            }
            
            return true;
        } else {
            showError(data.message || 'Login failed');
            return false;
        }
    } catch (error) {
        console.error('Login failed:', error);
        showError('Login failed. Please try again.');
        return false;
    }
}

// Logout user
function logoutUser() {
    localStorage.removeItem('auth_token');
    currentUser = null;
    showAuthModal();
}

// Start subscription after trial
async function startSubscription(paymentMethodId) {
    try {
        const token = localStorage.getItem('auth_token');
        const response = await fetch(`${API_URL}/api/syllabus-subscribe.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ paymentMethodId })
        });

        const data = await response.json();
        
        if (response.ok) {
            currentUser = data.user;
            hideSubscriptionModal();
            showSubscriptionSuccessModal();
            return true;
        } else {
            showError(data.message || 'Subscription failed');
            return false;
        }
    } catch (error) {
        console.error('Subscription failed:', error);
        showError('Subscription failed. Please try again.');
        return false;
    }
}

// UI Functions for authentication and subscription modals
function showAuthModal() {
    const modalHTML = `
        <div id="auth-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="auth-header">
                    <img src="https://app.getgradegenie.com/assets/logo.png" alt="Logo" class="auth-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iIzBEQkM5NSIvPjxwYXRoIGQ9Ik0xNyAyN0wyOCAxNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMTIgMTlMMTcgMjciIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+';">
                    <h1>Syllabus Generator</h1>
                </div>
                
                <div class="auth-tabs">
                    <button class="auth-tab active" data-tab="register">Start Free Trial</button>
                    <button class="auth-tab" data-tab="login">Login</button>
                </div>
                
                <div id="register-form" class="auth-form active">
                    <div class="trial-banner">
                        <div class="trial-icon">✨</div>
                        <h2>Start Your 3-Day Free Trial</h2>
                        <p>Get full access to all premium features</p>
                    </div>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Generate professional syllabi in minutes</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Download as PDF with one click</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Flexible cancellation — you maintain control</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="register-name">Full Name</label>
                        <input type="text" id="register-name" placeholder="John Smith" required>
                    </div>
                    <div class="form-group">
                        <label for="register-email">Work or .edu Email</label>
                        <input type="email" id="register-email" placeholder="john@school.edu" required>
                    </div>
                    <div class="form-group">
                        <label for="register-password">Password</label>
                        <input type="password" id="register-password" placeholder="••••••••" required>
                        <small class="password-hint">Password must be at least 8 characters</small>
                    </div>
                    <div class="card-section">
                        <h3>Payment Information</h3>
                        <p class="card-info">Your card will not be charged during the trial. After the trial, you'll be billed $18.99/month unless you cancel.</p>
                        <div id="card-element" class="form-group">
                            <!-- Stripe Card Element will be inserted here -->
                        </div>
                        <div id="card-errors" class="error-message"></div>
                    </div>
                    <button id="register-btn" class="btn-primary">Start Your Free, Effortless Teaching Journey</button>
                    <div class="secure-badge">
                        <span class="secure-icon">🔒</span> Secure login
                    </div>
                    <p class="terms">By continuing, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></p>
                </div>
                
                <div id="login-form" class="auth-form">
                    <h2>Welcome Back</h2>
                    <p class="login-subtitle">Log in to access your account</p>
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" placeholder="john@school.edu" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" placeholder="••••••••" required>
                    </div>
                    <button id="login-btn" class="btn-primary">Log in</button>
                    <div class="secure-badge">
                        <span class="secure-icon">🔒</span> Secure login
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Append modal to body if it doesn't exist
    if (!document.getElementById('auth-modal')) {
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHTML;
        document.body.appendChild(modalContainer);
        
        // Setup event listeners
        setupAuthModalEvents();
        setupStripeElements();
    } else {
        document.getElementById('auth-modal').style.display = 'block';
    }
}

function hideAuthModal() {
    const modal = document.getElementById('auth-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function showTrialStartedModal() {
    const modalHTML = `
        <div id="trial-started-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="auth-header">
                    <img src="https://app.getgradegenie.com/assets/logo.png" alt="Logo" class="auth-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iIzBEQkM5NSIvPjxwYXRoIGQ9Ik0xNyAyN0wyOCAxNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMTIgMTlMMTcgMjciIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+';">
                </div>
                <div class="success-banner">
                    <div class="success-icon">🎉</div>
                    <h2>Your 3-Day Free Trial Has Started!</h2>
                    <p>Welcome to the Syllabus Generator</p>
                </div>
                
                <div class="trial-info">
                    <p>You now have full access to all premium features for the next 3 days.</p>
                    <div class="trial-date">
                        <span class="date-label">Your trial will end on:</span>
                        <span class="date-value">${getTrialEndDate()}</span>
                    </div>
                    <p class="trial-note">We will not charge your card until the trial period ends.</p>
                </div>
                
                <div class="benefits-list">
                    <div class="benefit-item">
                        <span class="benefit-check">✓</span>
                        <span>Generate professional syllabi in minutes</span>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-check">✓</span>
                        <span>Download as PDF with one click</span>
                    </div>
                    <div class="benefit-item">
                        <span class="benefit-check">✓</span>
                        <span>Flexible cancellation — you maintain control</span>
                    </div>
                </div>
                
                <button id="trial-started-ok-btn" class="btn-primary">Get Started</button>
            </div>
        </div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    document.getElementById('trial-started-ok-btn').addEventListener('click', function() {
        document.getElementById('trial-started-modal').style.display = 'none';
    });
    
    document.querySelector('#trial-started-modal .close').addEventListener('click', function() {
        document.getElementById('trial-started-modal').style.display = 'none';
    });
}

function showTrialEndingNotification() {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-content">
            <p><strong>Your free trial is ending soon!</strong> Subscribe now to keep access to all features.</p>
            <button id="subscribe-now-btn" class="btn-primary">Subscribe Now</button>
            <button class="close-notification">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    document.getElementById('subscribe-now-btn').addEventListener('click', function() {
        notification.remove();
        showSubscriptionModal();
    });
    
    document.querySelector('.close-notification').addEventListener('click', function() {
        notification.remove();
    });
}

function showSubscriptionRequiredModal() {
    const modalHTML = `
        <div id="subscription-required-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="auth-header">
                    <img src="https://app.getgradegenie.com/assets/logo.png" alt="Logo" class="auth-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iIzBEQkM5NSIvPjxwYXRoIGQ9Ik0xNyAyN0wyOCAxNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMTIgMTlMMTcgMjciIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+';">
                </div>
                
                <div class="trial-banner trial-ended-banner">
                    <div class="trial-icon">⏰</div>
                    <h2>Your Free Trial Has Ended</h2>
                    <p>Continue enjoying premium features with a subscription</p>
                </div>
                
                <div class="subscription-features">
                    <h3>Keep access to all these features:</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Generate professional syllabi in minutes</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Download as PDF with one click</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Unlimited syllabus generation</span>
                        </div>
                    </div>
                </div>
                
                <div class="subscription-details">
                    <div class="price-badge">
                        <span class="price-amount">$18.99</span>
                        <span class="price-period">/month</span>
                    </div>
                    <ul class="price-features">
                        <li>Cancel anytime</li>
                        <li>Unlimited access</li>
                        <li>Priority support</li>
                    </ul>
                </div>
                
                <div class="card-section">
                    <h3>Payment Information</h3>
                    <div id="subscription-card-element" class="form-group">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    <div id="subscription-card-errors" class="error-message"></div>
                </div>
                
                <button id="complete-subscription-btn" class="btn-primary">Subscribe Now</button>
                
                <div class="secure-badge">
                    <span class="secure-icon">🔒</span> Secure payment
                </div>
                
                <button id="logout-btn" class="btn-text">I'll think about it later</button>
            </div>
        </div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Setup Stripe elements for subscription
    setupSubscriptionStripeElements();
    
    document.getElementById('complete-subscription-btn').addEventListener('click', handleSubscriptionSubmit);
    document.getElementById('logout-btn').addEventListener('click', function() {
        document.getElementById('subscription-required-modal').remove();
        logoutUser();
    });
}

function showSubscriptionModal() {
    const modalHTML = `
        <div id="subscription-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="auth-header">
                    <img src="https://app.getgradegenie.com/assets/logo.png" alt="Logo" class="auth-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iIzBEQkM5NSIvPjxwYXRoIGQ9Ik0xNyAyN0wyOCAxNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMTIgMTlMMTcgMjciIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+';">
                </div>
                
                <div class="trial-banner">
                    <div class="trial-icon">⭐</div>
                    <h2>Subscribe to Premium</h2>
                    <p>Continue enjoying all premium features</p>
                </div>
                
                <div class="subscription-features">
                    <h3>Your subscription includes:</h3>
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Generate professional syllabi in minutes</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Download as PDF with one click</span>
                        </div>
                        <div class="benefit-item">
                            <span class="benefit-check">✓</span>
                            <span>Unlimited syllabus generation</span>
                        </div>
                    </div>
                </div>
                
                <div class="subscription-details">
                    <div class="price-badge">
                        <span class="price-amount">$18.99</span>
                        <span class="price-period">/month</span>
                    </div>
                    <ul class="price-features">
                        <li>Cancel anytime</li>
                        <li>Unlimited access</li>
                        <li>Priority support</li>
                    </ul>
                </div>
                
                <div class="card-section">
                    <h3>Payment Information</h3>
                    <div id="subscription-card-element" class="form-group">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    <div id="subscription-card-errors" class="error-message"></div>
                </div>
                
                <button id="complete-subscription-btn" class="btn-primary">Subscribe Now</button>
                
                <div class="secure-badge">
                    <span class="secure-icon">🔒</span> Secure payment
                </div>
            </div>
        </div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // Setup Stripe elements for subscription
    setupSubscriptionStripeElements();
    
    document.getElementById('complete-subscription-btn').addEventListener('click', handleSubscriptionSubmit);
    document.querySelector('#subscription-modal .close').addEventListener('click', function() {
        document.getElementById('subscription-modal').remove();
    });
}

function hideSubscriptionModal() {
    const modal = document.getElementById('subscription-modal');
    if (modal) {
        modal.remove();
    }
    
    const requiredModal = document.getElementById('subscription-required-modal');
    if (requiredModal) {
        requiredModal.remove();
    }
}

function showSubscriptionSuccessModal() {
    const modalHTML = `
        <div id="subscription-success-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div class="auth-header">
                    <img src="https://app.getgradegenie.com/assets/logo.png" alt="Logo" class="auth-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIyMCIgZmlsbD0iIzBEQkM5NSIvPjxwYXRoIGQ9Ik0xNyAyN0wyOCAxNiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIzIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48cGF0aCBkPSJNMTIgMTlMMTcgMjciIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMyIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+PC9zdmc+';">
                </div>
                
                <div class="success-banner">
                    <div class="success-icon">✅</div>
                    <h2>Subscription Successful!</h2>
                    <p>Thank you for subscribing to our premium service</p>
                </div>
                
                <div class="success-message">
                    <p>You now have unlimited access to all features of the Syllabus Generator.</p>
                    <p>Start creating professional syllabi in minutes!</p>
                </div>
                
                <button id="subscription-success-ok-btn" class="btn-primary">Continue to Syllabus Generator</button>
            </div>
        </div>
    `;
    
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    document.getElementById('subscription-success-ok-btn').addEventListener('click', function() {
        document.getElementById('subscription-success-modal').remove();
    });
    
    document.querySelector('#subscription-success-modal .close').addEventListener('click', function() {
        document.getElementById('subscription-success-modal').remove();
    });
}

// Helper functions
function setupAuthModalEvents() {
    // Tab switching
    document.querySelectorAll('.auth-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            this.classList.add('active');
            const tabName = this.getAttribute('data-tab');
            document.getElementById(`${tabName}-form`).classList.add('active');
        });
    });
    
    // Close modal
    document.querySelector('#auth-modal .close').addEventListener('click', function() {
        document.getElementById('auth-modal').style.display = 'none';
    });
    
    // Login form submission
    document.getElementById('login-btn').addEventListener('click', async function() {
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        
        if (!email || !password) {
            showError('Please fill in all fields');
            return;
        }
        
        await loginUser(email, password);
    });
    
    // Register form will be handled by Stripe integration
}

// Stripe integration
let stripe;
let elements;
let cardElement;
let subscriptionCardElement;

function setupStripeElements() {
    // Hardcoded Stripe publishable key
    stripe = Stripe('pk_test_your_stripe_key');
    elements = stripe.elements();
    
    // Create card element
    cardElement = elements.create('card');
    cardElement.mount('#card-element');
    
    // Handle validation errors
    cardElement.addEventListener('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Handle registration form submission
    document.getElementById('register-btn').addEventListener('click', handleRegistrationSubmit);
}

function setupSubscriptionStripeElements() {
    if (!stripe) {
        stripe = Stripe('pk_test_your_stripe_key');
    }
    
    const elements = stripe.elements();
    subscriptionCardElement = elements.create('card');
    subscriptionCardElement.mount('#subscription-card-element');
    
    // Handle validation errors
    subscriptionCardElement.addEventListener('change', function(event) {
        const displayError = document.getElementById('subscription-card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

async function handleRegistrationSubmit(e) {
    e.preventDefault();
    
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    
    if (!name || !email || !password) {
        showError('Please fill in all fields');
        return;
    }
    
    const registerBtn = document.getElementById('register-btn');
    registerBtn.disabled = true;
    registerBtn.textContent = 'Processing...';
    
    try {
        // Create payment method
        const result = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {
                name: name,
                email: email
            }
        });
        
        if (result.error) {
            showError(result.error.message);
            registerBtn.disabled = false;
            registerBtn.textContent = 'Start Free Trial';
            return;
        }
        
        // Register user with payment method
        const response = await fetch(`${API_URL}/api/register-with-trial.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name,
                email,
                password,
                paymentMethodId: result.paymentMethod.id
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            localStorage.setItem('auth_token', data.token);
            currentUser = data.user;
            hideAuthModal();
            showTrialStartedModal();
        } else {
            showError(data.message || 'Registration failed');
        }
    } catch (error) {
        console.error('Registration failed:', error);
        showError('Registration failed. Please try again.');
    }
    
    registerBtn.disabled = false;
    registerBtn.textContent = 'Start Free Trial';
}

async function handleSubscriptionSubmit(e) {
    e.preventDefault();
    
    const subscribeBtn = document.getElementById('complete-subscription-btn');
    subscribeBtn.disabled = true;
    subscribeBtn.textContent = 'Processing...';
    
    try {
        // Create payment method
        const result = await stripe.createPaymentMethod({
            type: 'card',
            card: subscriptionCardElement
        });
        
        if (result.error) {
            showError(result.error.message, 'subscription-card-errors');
            subscribeBtn.disabled = false;
            subscribeBtn.textContent = 'Subscribe Now';
            return;
        }
        
        // Start subscription
        await startSubscription(result.paymentMethod.id);
    } catch (error) {
        console.error('Subscription failed:', error);
        showError('Subscription failed. Please try again.', 'subscription-card-errors');
    }
    
    subscribeBtn.disabled = false;
    subscribeBtn.textContent = 'Subscribe Now';
}

function showError(message, elementId = 'card-errors') {
    const errorElement = document.getElementById(elementId);
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function getTrialEndDate() {
    const date = new Date();
    date.setDate(date.getDate() + 3);
    return date.toLocaleDateString();
}

// Export functions for use in other scripts
window.authManager = {
    checkAuthStatus,
    showAuthModal,
    logoutUser,
    getCurrentUser: () => currentUser
};
