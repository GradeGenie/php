<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
        }
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }
        button {
            background-color: #16a085;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #138a72;
        }
        .toggle-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2ecc71;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .toggle-label {
            margin: 0 10px;
            font-weight: 600;
        }
        .testimonial-container {
            margin-top: 50px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .testimonial {
            border-radius: 8px;
            background-color: #ecf0f1;
            padding: 20px;
            max-width: 30%;
            text-align: left;
            font-style: italic;
        }
        .testimonial:before {
            content: """;
            font-size: 36px;
            color: #3498db;
            vertical-align: middle;
            margin-right: 10px;
        }
        .testimonial:after {
            content: """;
            font-size: 36px;
            color: #3498db;
            vertical-align: middle;
            margin-left: 10px;
        }
        .plans {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .plan-box {
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 250px;
            max-width: 30%;
            text-align: left;
        }
        .plan-box:hover {
            background-color: #f9f9f9;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .plan-box.active {
            border-color: #2ecc71;
            background-color: #eafaf1;
        }
        .plan-box h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .plan-price {
            font-size: 24px;
            font-weight: 600;
            color: #16a085;
            margin: 10px 0;
        }
        .plan-price .billing-info {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .plan-benefits {
            list-style-type: none;
            padding-left: 0;
        }
        .plan-benefits li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
        }
        .plan-benefits li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2ecc71;
            font-weight: bold;
        }
        form#signup-form {
            padding: 0px 300px;
            text-align: left;
        }
        @media (max-width: 600px) {
            .plans {
                flex-direction: column;
                align-items: center;
            }
            .plan-box {
                max-width: 100%;
            }
            form#signup-form {
                padding: 0px 20px;
            }
        }
        .signup-text {
            font-size: 14px;
            color: #666666;
            margin-top: 10px;
            text-align: center;
        }
        .signup-text a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .signup-text a:hover {
            color: #45a049;
        }
        #paymentHeader{
            margin-top: 50px;
        }

        #mainHeading{
            margin-top: 50px;
        }
        .plan-box {
            position: relative;
            padding-bottom: 75px;
        }
        .greenBtn {
            text-align: center;
            color: #fff;
            background: #16a085;
            padding: 12px;
            border-radius: 37px;
            font-weight: bold;
            position: absolute;
            bottom: 0;
            width: calc(100% - 40px);
            left: 0;
            margin: 20px;
        }
        /* Trial badge */
        .trial-badge {
            background-color: #16a085;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        /* Loading spinner */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #16a085;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .error-message {
            color: #e74c3c;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        /* Card element styling */
        .card-element {
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            background-color: white;
        }
        .card-errors {
            color: #e74c3c;
            text-align: left;
            margin-top: 10px;
            font-size: 14px;
        }
        .payment-form {
            margin-top: 30px;
            text-align: left;
        }
        .payment-success {
            display: none;
            text-align: center;
            padding: 20px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-top: 20px;
        }
        .steps-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 15px;
        }
        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ecf0f1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #7f8c8d;
        }
        .step-circle.active {
            background-color: #16a085;
            color: white;
        }
        .step-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        .step-label.active {
            color: #16a085;
            font-weight: bold;
        }
        .step-connector {
            width: 50px;
            height: 2px;
            background-color: #ecf0f1;
            margin-top: 15px;
        }
        .step-connector.active {
            background-color: #16a085;
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <!-- Steps indicator -->
        <div class="steps-container">
            <div class="step">
                <div class="step-circle active">1</div>
                <div class="step-label active">Account</div>
            </div>
            <div class="step-connector" id="connector-1"></div>
            <div class="step">
                <div class="step-circle" id="step-circle-2">2</div>
                <div class="step-label" id="step-label-2">Plan</div>
            </div>
            <div class="step-connector" id="connector-2"></div>
            <div class="step">
                <div class="step-circle" id="step-circle-3">3</div>
                <div class="step-label" id="step-label-3">Payment</div>
            </div>
        </div>

        <h1 id="mainHeading">Join 30,000+ Educators Saving Millions of Hours with GradeGenie</h1>
        
        <!-- Step 1: Account Information -->
        <div id="step-1-container">
            <div class="error-message" id="error-message-1"></div>
            <form id="signup-form">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Jane" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Smith" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="jane@school.edu" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="button" id="continue-to-step-2">Continue to Select Plan</button>
                <p class="signup-text">Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
        
        <!-- Step 2: Plan Selection -->
        <div id="step-2-container" style="display:none;">
            <h2 id="paymentHeader">Select a Payment Plan <span class="trial-badge">3-Day Free Trial</span></h2>
            <p>Try GradeGenie free for 3 days. No charges during trial period.</p>
            
            <div class="toggle-container">
                <span class="toggle-label">Monthly</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="yearly-toggle" checked>
                    <span class="slider"></span>
                </label>
                <span class="toggle-label">Yearly</span>
            </div>
            
            <div class="plans">
                <!-- Educator Plan -->
                <div class="plan-box" id="educator_plan" onclick="selectPlan('educator_plan')">
                    <h3>Solo</h3>
                    <p class="plan-price" id="educator_price">$14.99/month </p><span class="billing-info" id="educator_billing"></span>
                    <ul class="plan-benefits">
                        <li>Grade up to 400 assignments 10x faster, for 1</li>
                        <li>Create detailed and targeted rubrics in seconds</li>
                        <li>Rubric Library to manage and update both uploaded and custom rubrics</li>
                        <li>Research-backed feedback designed to enhance performance</li>
                        <li>Progress Overview & Class Performance Insights</li>
                        <li>Manage your classes, assignments, and submissions efficiently</li>
                        <li>Priority Support</li>
                        <div class="greenBtn">Choose Plan</div>
                    </ul>
                </div>
                <!-- School Plan -->
                <div class="plan-box" id="school_plan" onclick="selectPlan('school_plan')">
                    <h3>Team</h3>
                    <p class="plan-price" id="school_price">$749.50/month </p><span class="billing-info" id="school_billing"></span>
                    <ul class="plan-benefits">
                        <li>Unlimited grading assistance for up to 50</li>
                        <li>Bulk grading capabilities for large classes</li>
                        <li>AI usage Detection</li>
                        <li>Plagiarism Detection</li>
                        <li>Citation Checker</li>
                        <li>Custom LMS Integrations</li>
                        <li>Grammar and Spelling Checker</li>
                        <li>Custom rubric generator with advanced customization options</li>
                        <li>Tailored training and support</li>
                        <li>Dedicated Account Manager</li>
                        <div class="greenBtn">Choose Plan</div>
                    </ul>
                </div>
                <!-- Uni Plan -->
                <div class="plan-box" id="uni_plan" onclick="selectPlan('uni_plan')">
                    <h3>Pro</h3>
                    <p class="plan-price" id="uni_price">$1,499.00/month </p><span class="billing-info" id="uni_billing"></span>
                    <ul class="plan-benefits">
                        <li>Unlimited grading assistance for up to 100</li>
                        <li>Bulk grading capabilities for large classes</li>
                        <li>AI usage Detection</li>
                        <li>Plagiarism Detection</li>
                        <li>Citation Checker</li>
                        <li>Custom LMS Integrations</li>
                        <li>Grammar and Spelling Checker</li>
                        <li>Custom rubric generator with advanced customization options</li>
                        <li>Tailored training and support</li>
                        <li>Dedicated Account Manager</li>
                        <div class="greenBtn">Choose Plan</div>
                    </ul>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="button" id="back-to-step-1">Back</button>
            </div>
        </div>
        
        <!-- Step 3: Payment Information -->
        <div id="step-3-container" style="display:none;">
            <h2>Payment Information <span class="trial-badge">3-Day Free Trial</span></h2>
            <p>Your card will not be charged until after your 3-day free trial.</p>
            
            <div class="error-message" id="error-message-3"></div>
            
            <div class="payment-form">
                <div class="form-group">
                    <label for="card-element">Credit or debit card</label>
                    <div id="card-element" class="card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    <div id="card-errors" class="card-errors" role="alert"></div>
                </div>
                
                <button type="button" id="submit-payment">Start 3-Day Free Trial</button>
                <p class="signup-text">By clicking above, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
            </div>
            
            <div class="payment-success" id="payment-success">
                <i class="fas fa-check-circle" style="font-size: 48px; color: #2ecc71; margin-bottom: 20px;"></i>
                <h2>Your 3-Day Free Trial Has Started!</h2>
                <p>We've sent a confirmation email to your inbox. You'll be redirected to your dashboard in a moment.</p>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="button" id="back-to-step-2">Back</button>
            </div>
        </div>
    </div>

    <div class="testimonial-container">
        <div class="testimonial">
            "GradeGenie saved me weeks of work. My students appreciate the fast turnaround, and I love how quick, easy, and accurate it is."
            <br><strong>- Hannah, School Teacher</strong>
        </div>
        <div class="testimonial">
            "Finding and managing TAs used to be a nightmare. With GradeGenie, I have a reliable, consistent grading assistant, freeing up my time for research."
            <br><strong>- Dane, University Professor</strong>
        </div>
        <div class="testimonial">
            "The feedback it provides is much more detailed than I have time to give, and I can still add my comments and adjust grades. I can't imagine going back to the old way!"
            <br><strong>- Doras, Tutor</strong>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Global variables
        let selectedPlan = null;
        let priceId = '';
        let stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
        let elements = stripe.elements();
        let card;
        
        // DOM elements
        const step1Container = document.getElementById('step-1-container');
        const step2Container = document.getElementById('step-2-container');
        const step3Container = document.getElementById('step-3-container');
        const stepCircle2 = document.getElementById('step-circle-2');
        const stepLabel2 = document.getElementById('step-label-2');
        const stepCircle3 = document.getElementById('step-circle-3');
        const stepLabel3 = document.getElementById('step-label-3');
        const connector1 = document.getElementById('connector-1');
        const connector2 = document.getElementById('connector-2');
        const loadingOverlay = document.getElementById('loading-overlay');
        const errorMessage1 = document.getElementById('error-message-1');
        const errorMessage3 = document.getElementById('error-message-3');
        
        // Initialize Stripe Elements
        function initializeStripeElements() {
            // Create an instance of the card Element
            card = elements.create('card', {
                style: {
                    base: {
                        color: '#32325d',
                        fontFamily: '"Albert Sans", sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#e74c3c',
                        iconColor: '#e74c3c'
                    }
                }
            });
            
            // Add an instance of the card Element into the `card-element` div
            card.mount('#card-element');
            
            // Handle real-time validation errors from the card Element
            card.addEventListener('change', function(event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });
        }
        
        // Step 1 to Step 2
        document.getElementById('continue-to-step-2').addEventListener('click', function() {
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Simple validation
            if (!firstName || !lastName || !email || !password) {
                errorMessage1.textContent = 'Please fill in all fields';
                errorMessage1.style.display = 'block';
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessage1.textContent = 'Please enter a valid email address';
                errorMessage1.style.display = 'block';
                return;
            }
            
            // Password validation (at least 6 characters)
            if (password.length < 6) {
                errorMessage1.textContent = 'Password must be at least 6 characters long';
                errorMessage1.style.display = 'block';
                return;
            }
            
            // Hide error message if validation passes
            errorMessage1.style.display = 'none';
            
            // Show step 2
            step1Container.style.display = 'none';
            step2Container.style.display = 'block';
            
            // Update steps indicator
            stepCircle2.classList.add('active');
            stepLabel2.classList.add('active');
            connector1.classList.add('active');
        });
        
        // Step 2 back to Step 1
        document.getElementById('back-to-step-1').addEventListener('click', function() {
            step2Container.style.display = 'none';
            step1Container.style.display = 'block';
            
            // Update steps indicator
            stepCircle2.classList.remove('active');
            stepLabel2.classList.remove('active');
            connector1.classList.remove('active');
        });
        
        // Step 3 back to Step 2
        document.getElementById('back-to-step-2').addEventListener('click', function() {
            step3Container.style.display = 'none';
            step2Container.style.display = 'block';
            
            // Update steps indicator
            stepCircle3.classList.remove('active');
            stepLabel3.classList.remove('active');
            connector2.classList.remove('active');
        });
        
        // Select plan function
        function selectPlan(plan) {
            const allPlans = document.querySelectorAll('.plan-box');
            allPlans.forEach(box => box.classList.remove('active'));
            
            document.getElementById(plan).classList.add('active');
            selectedPlan = plan;
            
            // Get appropriate price ID based on plan and billing cycle
            const yearlyBilling = document.getElementById('yearly-toggle').checked;
            
            if (selectedPlan === 'educator_plan' && !yearlyBilling) {
                priceId = 'price_1PmX7dAIe95LGsScfRPzMv6E';
            } else if (selectedPlan === 'educator_plan' && yearlyBilling) {
                priceId = 'price_1PmX8UAIe95LGsSclb7Nj40E';
            } else if (selectedPlan === 'school_plan' && !yearlyBilling) {
                priceId = 'price_1PnrGWAIe95LGsScZaGXeRwd';
            } else if (selectedPlan === 'school_plan' && yearlyBilling) {
                priceId = 'price_1PnozdAIe95LGsSccmEKIi9R';
            } else if (selectedPlan === 'uni_plan' && !yearlyBilling) {
                priceId = 'price_1PmX9eAIe95LGsScgcAeNj9r';
            } else if (selectedPlan === 'uni_plan' && yearlyBilling) {
                priceId = 'price_1PmXAtAIe95LGsScShVpe3X7';
            }
            
            // Show step 3
            step2Container.style.display = 'none';
            step3Container.style.display = 'block';
            
            // Update steps indicator
            stepCircle3.classList.add('active');
            stepLabel3.classList.add('active');
            connector2.classList.add('active');
            
            // Initialize Stripe Elements when showing step 3
            if (!card) {
                initializeStripeElements();
            }
        }
        
        // Submit payment function
        document.getElementById('submit-payment').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            loadingOverlay.style.display = 'flex';
            
            // Get user data
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Create a token or payment method
            stripe.createPaymentMethod({
                type: 'card',
                card: card,
                billing_details: {
                    name: firstName + ' ' + lastName,
                    email: email
                },
            }).then(function(result) {
                if (result.error) {
                    // Show error in the form
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    loadingOverlay.style.display = 'none';
                } else {
                    // Send the payment method ID to your server
                    createSubscriptionWithTrial({
                        email: email,
                        first_name: firstName,
                        last_name: lastName,
                        password: password,
                        price_id: priceId,
                        payment_method_id: result.paymentMethod.id
                    });
                }
            });
        });
        
        // Function to create subscription with trial
        function createSubscriptionWithTrial(data) {
            // In a production environment, this would call your API endpoint
            // For this demo, we'll simulate the API call
            
            // Option 1: Call your API endpoint
            /*
            fetch('api/create-subscription-with-trial.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                loadingOverlay.style.display = 'none';
                if (result.success) {
                    // Show success message
                    document.getElementById('payment-success').style.display = 'block';
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = 'trial-success-demo.php';
                    }, 3000);
                } else {
                    // Show error
                    errorMessage3.textContent = result.message || 'An error occurred';
                    errorMessage3.style.display = 'block';
                }
            })
            .catch(error => {
                loadingOverlay.style.display = 'none';
                errorMessage3.textContent = 'An error occurred. Please try again.';
                errorMessage3.style.display = 'block';
            });
            */
            
            // Option 2: For demo purposes, simulate success
            setTimeout(function() {
                // Hide loading overlay
                loadingOverlay.style.display = 'none';
                
                // Store user information in session
                sessionStorage.setItem('user_name', data.first_name + ' ' + data.last_name);
                sessionStorage.setItem('user_email', data.email);
                sessionStorage.setItem('trial_end_date', new Date(Date.now() + 3*24*60*60*1000).toISOString());
                
                // Show success message
                document.getElementById('payment-success').style.display = 'block';
                
                // Redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = 'trial-success-demo.php';
                }, 3000);
            }, 2000);
        }
        
        // Toggle between monthly and yearly pricing
        document.getElementById('yearly-toggle').addEventListener('change', function() {
            const isYearly = this.checked;
            
            document.getElementById('educator_price').textContent = isYearly ? '$12.49/month' : '$14.99/month';
            document.getElementById('educator_billing').textContent = isYearly ? '$149.88 billed yearly (Save 25%)' : '';
            
            document.getElementById('school_price').textContent = isYearly ? '$624.50/month' : '$749.50/month';
            document.getElementById('school_billing').textContent = isYearly ? '$7,494 billed yearly (Save $3,000)' : '';
            
            document.getElementById('uni_price').textContent = isYearly ? '$1,249.00/month' : '$1,499.00/month';
            document.getElementById('uni_billing').textContent = isYearly ? '$14,988 billed yearly (Save $3,000)' : '';
        });
        
        // Trigger change event on page load to show yearly pricing
        window.onload = function() {
            document.getElementById('yearly-toggle').dispatchEvent(new Event('change'));
        };
    </script>
</body>
</html>
