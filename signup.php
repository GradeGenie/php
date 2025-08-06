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
        <link rel="icon" href="https://app.getgradegenie.com/assets/ggfav.png" sizes="32x32" type="image/png">
         <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Onest:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
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
            font-family: 'Onest', sans-serif;
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
            border-color: #19A37E;
            outline: none;
        }
        button {
            font-family: 'Inter', sans-serif;
            font-weight: bold;
            background-color: #19A37E;
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
            background-color: #19A37E;
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
            background-color: #19A37E;
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
            padding: 0px 20px;
            text-align: left;
        }
        
        .signup-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            align-items: flex-start;
            margin-top: 20px;
        }
        
        .form-container {
            flex: 1;
            min-width: 300px;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .benefits-container {
            flex: 1;
            min-width: 300px;
            padding: 20px;
        }
        
        .benefit-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .benefit-icon {
            color: #16a085;
            font-size: 24px;
            margin-right: 15px;
            margin-top: 2px;
        }
        
        .benefit-text {
        }
        
        .benefit-text h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            text-align: left;
        }
        
        .benefit-text p {
            margin: 0;
            color: #666;
            font-size: 14px;
            text-align: left;
        }
        
        .social-proof {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .social-proof h3 {
            margin-top: 0;
            font-size: 16px;
            color: #333;
        }
        
        .reviews {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .review {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            position: relative;
        }
        
        .review::before {
            content: '"';
            font-size: 30px;
            color: #e0e0e0;
            position: absolute;
            left: 5px;
            top: 0;
        }
        
        .review-author {
            font-weight: bold;
            margin-top: 10px;
            text-align: right;
            font-size: 13px;
        }
        
        .sticky-cta {
            position: sticky;
            bottom: 20px;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-top: 20px;
            z-index: 100;
            display: none;
        }
        
        .sticky-cta button {
            background-color: #16a085;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .sticky-cta button:hover {
            background-color: #138a72;
        }
        
        .sticky-cta p {
            margin: 10px 0 0 0;
            font-size: 13px;
            color: #666;
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
            width: calc(100% - 60px);
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
        /* Checkout styling */
        .checkout-summary {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .checkout-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .item-name {
            font-weight: 600;
        }
        .checkout-trial {
            background-color: #e8f8f5;
            color: #16a085;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 15px;
            text-align: center;
        }
        .checkout-trial i {
            margin-right: 8px;
        }
        .checkout-button {
            background-color: #16a085;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .checkout-button i {
            margin-right: 10px;
        }
        .checkout-button:hover {
            background-color: #138a72;
        }
        .secure-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 24px;
        }
        .secure-badges i:first-child {
            font-size: 16px;
            margin-right: 5px;
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

        <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
        <div class="error-message" style="display: block; margin-bottom: 20px; text-align: center;">
            <i class="fas fa-exclamation-circle"></i> Checkout was cancelled, please try again
        </div>
        <?php endif; ?>

        <h1 id="mainHeading">Grade Papers 100x Faster with AI</h1>
        <p style="font-size: 18px; margin-bottom: 30px;">Join 30,000+ educators saving hours every week. <span style="background-color: #fffbea; padding: 2px 8px; border-radius: 4px; color: #19A37E; font-weight: bold;">Start FREE today</span></p>
        
        <!-- Step 1: Account Information -->
        <div id="step-1-container">
            <div class="signup-container">
                <div class="form-container">
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
                        <button type="button" id="continue-to-step-2">Start Free Trial <span style="font-size: 12px; background-color: #19A37E; color: white; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">FREE</span></button>
                        <p style="text-align: center; margin-top: 10px; color: #666;"><i class="fas fa-check-circle" style="color: #19A37E;"></i> No payment charged during trial</p>
                        <p class="signup-text">Already have an account? <a href="login.php">Sign in</a></p>
                    </form>
                </div>
                
                <div class="benefits-container">
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
                        <div class="benefit-text">
                            <h3>Grading Made Effortless. Feedback Students Love.</h3>
                            <p>Grade hundreds of papers in seconds with One-Click Bulk Grading.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="benefit-text">
                            <h3>Better Feedback for Students</h3>
                            <p>Provide evidence-based feedback that highlights strengths and areas for improvement.</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-clock"></i></div>
                        <div class="benefit-text">
                            <h3>Save 10+ Hours Every Week</h3>
                            <p>Reclaim your time with automated grading assistance that doesn't compromise quality.</p>
                        </div>
                    </div>
                    
                    <div class="social-proof">
                        <h3>What Educators Are Saying</h3>
                        <div class="reviews">
                            <div class="review">
                                "GradeGenie saved me weeks of work. My students appreciate the fast turnaround, and I love how quick and accurate it is."
                                <div class="review-author">- Hannah T., High School Teacher</div>
                            </div>
                            <div class="review">
                                "Finding and managing TAs used to be a nightmare. With GradeGenie, I have a reliable grading assistant, freeing up my time for research."
                                <div class="review-author">- Dr. James K., University Professor</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="sticky-cta">
                <button type="button" onclick="document.getElementById('continue-to-step-2').click();">Start Your Free Trial Now</button>
                <p><i class="fas fa-check-circle"></i> No credit card required</p>
            </div>
        </div>
        
        <!-- Step 2: Plan Selection -->
        <div id="step-2-container" style="display:none;">
            <h3 style="margin-top: 30px; text-align: center;">Choose Your Plan</h3>
            
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
                    <h3>Educator Plan</h3>
                    <p class="plan-price" id="educator_price">$18.99 / seat / month </p>
                    <p class="billing-info" id="educator_billing">$16.99 / seat / month, billed yearly</p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">For individual teachers who want to save time and give better feedback.</p>
                    <p style="font-weight: bold; margin-bottom: 10px;">You get:</p>
                    <ul class="plan-benefits">
                        <li>Grade hundreds of papers in seconds with One-Click Bulk Grading</li>
                        <li>Final score auto-calculated from weighted subscores and clear rationale</li>
                        <li>Evidence-based feedback highlighting strengths, weaknesses, and improvements</li>
                        <li>Inline comments directly on student submissions</li>
                        <li>GradeGenie creates rubrics, syllabi, and assignment briefs for you so you don't have to</li>
                        <li>Export results as shareable PDF reports</li>
                        <div class="greenBtn">Start 3-Day Free Trial</div>
                    </ul>
                </div>
                <!-- Institution Plan -->
                <div class="plan-box" id="school_plan" onclick="selectPlan('school_plan')">
                    <h3>Institution Plan</h3>
                    <p class="plan-price" id="school_price">$19.99 / seat / month</p>
                    <p class="billing-info" id="school_billing">$17.99 / seat / month (min 3 seats), billed yearly</p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">For teams and schools who need collaboration and shared grading.</p>
                    <p style="font-weight: bold; margin-bottom: 10px;">Everything in Educator, plus:</p>
                    <ul class="plan-benefits">
                        <li>Pre-built rubrics and feedback templates for consistent grading</li>
                        <li>Collaborate with your team on assignments</li>
                        <li>Role-based access for teachers, TAs, and assistants</li>
                        <li>Track grading progress with a team dashboard</li>
                        <li>Centralized billing for simplified payments</li>
                        <div class="greenBtn">Speak to Us</div>
                    </ul>
                </div>
                <!-- Enterprise Plan -->
                <div class="plan-box" id="uni_plan" onclick="selectPlan('uni_plan')">
                    <h3>Enterprise Plan</h3>
                    <p class="plan-price" id="uni_price">Custom Pricing</p>
                    <p class="billing-info" id="uni_billing"></p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">For universities and institutions needing advanced tools and support.</p>
                    <p style="font-weight: bold; margin-bottom: 10px;">Everything in Institution, plus:</p>
                    <ul class="plan-benefits">
                        <li>Plagiarism detection and originality checks</li>
                        <li>Student Self-Check Portal for pre-submission feedback</li>
                        <li>Support for advanced assignment types and formats</li>
                        <li>Custom LMS integrations for seamless workflows</li>
                        <li>Advanced analytics and reporting for educators and admins</li>
                        <li>Dedicated relationship manager and priority support</li>
                        <li>Volume-based pricing and centralized admin controls</li>
                        <div class="greenBtn">Speak to Us</div>
                    </ul>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="button" id="back-to-step-1">Back</button>
            </div>
        </div>
        
        <!-- Step 3: Payment Information -->
        <div id="step-3-container" style="display:none;">
            <h2>Complete Your Registration <span class="trial-badge">FREE TRIAL</span></h2>
            <p>Enter your payment details to start using GradeGenie. <strong>No charges during your trial.</strong></p>
            
            <div class="error-message" id="error-message-3"></div>
            
            <div class="payment-form">
                <div class="form-group">
                    <label for="card-element">Credit or debit card</label>
                    <div id="card-element" class="card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    <div class="checkout-trial">
                        <i class="fas fa-gift"></i> 3-Day Free Trial
                    </div>
                </div>
                
                <button id="submit-payment" class="checkout-button">
                    <i class="fas fa-lock"></i> Proceed to Secure Checkout
                </button>
                
                <div class="secure-badges">
                    <i class="fas fa-lock"></i> Secure Checkout
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                    <i class="fab fa-cc-discover"></i>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="button" id="back-to-step-2">Back</button>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Global variables
        let selectedPlan = null;
        let priceId = '';
        // Use the Stripe publishable key from c.php
        let stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
        
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
        
        // Update checkout price display based on plan selection
        function updateCheckoutPrice(isYearly) {
            const checkoutPrice = document.getElementById('checkout-price');
            if (checkoutPrice) {
                if (isYearly) {
                    checkoutPrice.textContent = '$16.99/month (billed yearly)';
                } else {
                    checkoutPrice.textContent = '$18.99/month';
                }
            }
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
            
            // Only proceed with subscription for the Educator plan
            // Institution and Enterprise plans redirect to contact
            if (selectedPlan === 'educator_plan') {
                if (!yearlyBilling) {
                    // Monthly Educator plan - $18.99/month
                    priceId = 'price_1RSXFEAIe95LGsScYUJIPnvs';
                } else {
                    // Yearly Educator plan - $16.99/month billed yearly
                    priceId = 'price_1RSXFEAIe95LGsSccDAdZWfJ';
                }
                
                // Validate user information before proceeding to checkout
                const firstName = document.getElementById('first_name').value;
                const lastName = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                // Simple validation
                if (!firstName || !lastName || !email || !password) {
                    alert('Please fill in all required fields in the Account step first');
                    // Go back to step 1
                    step2Container.style.display = 'none';
                    step1Container.style.display = 'block';
                    // Update steps indicator
                    stepCircle2.classList.remove('active');
                    stepLabel2.classList.remove('active');
                    connector1.classList.remove('active');
                    return;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address');
                    // Go back to step 1
                    step2Container.style.display = 'none';
                    step1Container.style.display = 'block';
                    // Update steps indicator
                    stepCircle2.classList.remove('active');
                    stepLabel2.classList.remove('active');
                    connector1.classList.remove('active');
                    return;
                }
                
                // Password validation (at least 6 characters)
                if (password.length < 6) {
                    alert('Password must be at least 6 characters long');
                    // Go back to step 1
                    step2Container.style.display = 'none';
                    step1Container.style.display = 'block';
                    // Update steps indicator
                    stepCircle2.classList.remove('active');
                    stepLabel2.classList.remove('active');
                    connector1.classList.remove('active');
                    return;
                }
                
                // Show loading overlay
                loadingOverlay.style.display = 'flex';
                
                // Create checkout session and redirect to Stripe directly
                createCheckoutSession({
                    email: email,
                    firstName: firstName,
                    lastName: lastName,
                    password: password,
                    priceId: priceId
                });
            } else {
                // For Institution and Enterprise plans, redirect to contact page
                window.location.href = 'contact.php?plan=' + selectedPlan;
                return;
            }
        }
        
        // Submit payment function - redirects to Stripe Checkout
        document.getElementById('submit-payment').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            loadingOverlay.style.display = 'flex';
            
            // Get user data
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Create checkout session and redirect to Stripe
            createCheckoutSession({
                email: email,
                firstName: firstName,
                lastName: lastName,
                password: password,
                priceId: priceId
            });
        });
        
        // Function to create checkout session and redirect to Stripe
        async function createCheckoutSession(data) {
            try {
                const response = await fetch('/api/create-checkout-session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    // Log detailed error information to console
                    console.error('Checkout Session Error:', {
                        status: response.status,
                        statusText: response.statusText,
                        error: result
                    });
                    
                    // If there are detailed error details, log them specifically
                    if (result.error_details) {
                        console.error('Detailed Error Information:', result.error_details);
                    }
                    
                    throw new Error(result.message || 'Failed to create checkout session');
                }

                if (!result.id || !result.url) {
                    console.error('Unexpected response format:', result);
                    throw new Error('Invalid response from server');
                }

                // Store the checkout URL for later use
                const checkoutUrl = result.url;

                // Create the user with the checkout session ID
                const userResponse = await fetch('api/signup_create_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        first_name: data.firstName,
                        last_name: data.lastName,
                        email: data.email,
                        password: data.password,
                        stripeID: result.id // Use the checkout session ID
                    })
                });

                const userResult = await userResponse.json();

                if (!userResult.success) {
                    console.error('User Creation Error:', userResult);
                    throw new Error(userResult.message || 'Error creating user');
                }

                // If everything is successful, redirect to Stripe Checkout
                window.location.href = checkoutUrl;

            } catch (error) {
                console.error('Checkout Session Creation Failed:', error);
                loadingOverlay.style.display = 'none';
                errorMessage3.textContent = error.message || 'An error occurred. Please try again.';
                errorMessage3.style.display = 'block';
            }
        }
        
        // Toggle between monthly and yearly pricing
        document.getElementById('yearly-toggle').addEventListener('change', function() {
            const isYearly = this.checked;
            
            // Update Educator Plan pricing
            if (isYearly) {
                document.getElementById('educator_price').textContent = '$16.99 / seat / month';
                document.getElementById('educator_billing').textContent = '$16.99 / seat / month, billed yearly';
                // Update price ID for yearly billing
                priceId = 'price_1RSXFEAIe95LGsSccDAdZWfJ';
            } else {
                document.getElementById('educator_price').textContent = '$18.99 / seat / month';
                document.getElementById('educator_billing').textContent = '';
                // Update price ID for monthly billing
                priceId = 'price_1RSXFEAIe95LGsScYUJIPnvs';
            }
            
            // Update Institution Plan pricing
            if (isYearly) {
                document.getElementById('school_price').textContent = '$17.99 / seat / month (min 3 seats)';
                document.getElementById('school_billing').textContent = '$17.99 / seat / month (min 3 seats), billed yearly';
            } else {
                document.getElementById('school_price').textContent = '$19.99 / seat / month (min 3 seats)';
                document.getElementById('school_billing').textContent = '';
            }
            
            // Enterprise plan doesn't change with billing cycle
            document.getElementById('uni_price').textContent = 'Custom Pricing (min 3 seats)';
            document.getElementById('uni_billing').textContent = '';
            
            // Update checkout price display if we're on step 3
            if (step3Container.style.display === 'block') {
                updateCheckoutPrice(isYearly);
            }
        });
        
        // Trigger change event on page load to show yearly pricing
        window.onload = function() {
            document.getElementById('yearly-toggle').dispatchEvent(new Event('change'));
        };
    </script>
</body>
</html>
