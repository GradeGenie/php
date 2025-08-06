<?php
// DIRECT ERROR REPORTING FOR DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to display errors directly on the page
function show_error($title, $message) {
    echo "<div style='margin: 20px; padding: 20px; border: 2px solid #dc3545; background-color: #f8d7da; color: #721c24; border-radius: 5px;'>";
    echo "<h3 style='margin-top: 0;'>$title</h3>";
    echo "<p>$message</p>";
    echo "</div>";
}

// Start session
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} catch (Exception $e) {
    show_error("Session Error", "Error starting session: " . $e->getMessage());
}

// Include database connection with error handling
try {
    // Define a flag to track if c.php has been included
    if (!defined('DB_INCLUDED')) {
        try {
            // Log the current directory and file for debugging
            error_log("Current directory: " . __DIR__);
            error_log("Current file: " . __FILE__);
            
            // Check if file exists before requiring it
            if (!file_exists('api/c.php')) {
                show_error("File Not Found", "api/c.php does not exist. Current directory: " . __DIR__);
            } else {
                require_once 'api/c.php';
            }
        } catch (Exception $e) {
            show_error("Database Connection Error", "Error connecting to database: " . $e->getMessage());
        }
        define('DB_INCLUDED', true);
    }
} catch (Exception $e) {
    show_error("Database Connection Error", "Error connecting to database: " . $e->getMessage());
    exit;
}

// Debug information for query parameters
if (isset($_GET['from']) && $_GET['from'] === 'signup') {
    echo "<!-- Debug: Accessed from signup -->";
    echo "<!-- Session data: " . json_encode($_SESSION) . " -->";
}

// We'll use the show_error function for error handling
// No need to include the centralized error handler as it's causing conflicts

// Handle error messages
$errorMessage = null;
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_session':
            $errorMessage = 'Your session has expired. Please try again.';
            break;
        case 'payment_failed':
            $errorMessage = 'Your payment could not be processed. Please try again or use a different payment method.';
            break;
        case 'system_error':
            $errorMessage = 'We encountered a system error. Our team has been notified.';
            break;
        default:
            $errorMessage = 'An error occurred. Please try again.';
    }
    
    // Display the error message to the user
    if ($errorMessage) {
        show_error("Error", $errorMessage);
    }
    
    // Debug comment for error message
    echo "<!-- Debug: Error displayed | Error: {$_GET['error']} | Message: {$errorMessage} -->";
}

// Debug comment for page visit
echo "<!-- Debug: Page visited | Referrer: " . ($_SERVER['HTTP_REFERER'] ?? 'direct') . " | From signup: " . (isset($_GET['from']) && $_GET['from'] === 'signup' ? 'yes' : 'no') . " -->";

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;
$userEmail = $isLoggedIn && isset($_SESSION['email']) ? $_SESSION['email'] : '';
$userName = $isLoggedIn && isset($_SESSION['name']) ? $_SESSION['name'] : '';

// Get subscription status if logged in
$subscriptionActive = false;
$trialActive = false;
$trialEndsDate = null;

if ($isLoggedIn) {
    try {
        // Create database connection
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check subscription status
        $stmt = $conn->prepare("SELECT subscription_status, trial_ends_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $subscriptionActive = ($user['subscription_status'] === 'active');
            
            // Check if trial is active
            if (!empty($user['trial_ends_at'])) {
                $trialEndsAt = strtotime($user['trial_ends_at']);
                $now = time();
                $trialActive = ($trialEndsAt > $now);
                $trialEndsDate = date('F j, Y', $trialEndsAt);
            }
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // Handle error silently
        error_log("Error checking subscription: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https://js.stripe.com https://api.stripe.com; font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://js.stripe.com https://ajax.googleapis.com; img-src 'self' data: https:; connect-src 'self' https://api.stripe.com;">
    <title>Pricing Plans | GradeGenie</title>
    <link rel="manifest" href="/site.webmanifest">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js-fixes.js?v=<?php echo rand(111111, 999999); ?>"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: 'Albert Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .pricing-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .pricing-header h1 {
            font-size: 2.5rem;
            color: #28a745;
            margin-bottom: 10px;
        }
        
        .pricing-header p {
            font-size: 1.2rem;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .pricing-toggle {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 30px 0;
            gap: 20px;
        }
        
        .toggle-option {
            font-size: 1.1rem;
            font-weight: bold;
            color: #666;
            cursor: pointer;
        }
        
        .toggle-option.active {
            color: #28a745;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
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
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background-color: #28a745;
        }
        
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .save-badge {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 5px;
        }
        
        .pricing-plans {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        
        .pricing-plan {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 300px;
            transition: transform 0.3s ease;
        }
        
        .pricing-plan:hover {
            transform: translateY(-10px);
        }
        
        .pricing-plan.popular {
            border: 2px solid #28a745;
            position: relative;
        }
        
        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .plan-name {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .plan-price {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #28a745;
        }
        
        .plan-price .period {
            font-size: 0.9rem;
            color: #666;
            display: block;
            font-weight: normal;
        }
        
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 30px 0;
        }
        
        .plan-features li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: flex-start;
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features i {
            color: #28a745;
            margin-right: 10px;
            margin-top: 5px;
        }
        
        .plan-button {
            display: block;
            width: 100%;
            padding: 12px 0;
            background-color: #28a745;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .plan-button:hover {
            background-color: #218838;
        }
        
        .plan-button.outline {
            background-color: transparent;
            border: 2px solid #28a745;
            color: #28a745;
        }
        
        .plan-button.outline:hover {
            background-color: #28a745;
            color: white;
        }
        
        .trial-notice {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .trial-banner {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .trial-banner h3 {
            margin-top: 0;
            color: #28a745;
        }
        
        .trial-banner p {
            margin-bottom: 0;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 500px;
            max-width: 90%;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        #card-element {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        #card-errors {
            color: #dc3545;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        /* Ensure pricing plans are displayed horizontally on desktop */
        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .col-md-4 {
            flex: 0 0 calc(33.333333% - 20px);
            max-width: calc(33.333333% - 20px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .col-md-4:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Make sure cards are the same height */
        .card.h-100 {
            height: 100% !important;
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card-header {
            padding: 25px 15px;
            border-bottom: none;
        }
        
        .card-body {
            padding: 30px 25px;
        }
        
        .plan-button {
            padding: 12px 0;
            border-radius: 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .plan-button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .pricing-toggle {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            margin-bottom: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .toggle-option {
            padding: 8px 20px;
            cursor: pointer;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .toggle-option.active {
            background-color: #3498db;
            color: white;
        }
        
        .save-badge {
            background-color: #2ecc71;
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 30px;
            }
        }
        
        /* Show modal class */
        .modal.show {
            display: block !important;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <!-- No registration modal included -->
    
    <div id="mainContent" class="container">
        <div class="pricing-header text-center mb-5">
            <h1 class="display-4" style="color: #2c3e50; font-weight: 700; margin-bottom: 20px;">Choose Your Plan</h1>
            <div class="pricing-subtitle" style="max-width: 700px; margin: 0 auto;">
                <p class="lead" style="color: #3498db; font-size: 24px; margin-bottom: 15px;">Grading Made Effortless. Feedback Students Love.</p>
                <p style="color: #7f8c8d; font-size: 18px;">Save hours every week with AI-powered grading that's fast, fair, and transparent.</p>
            </div>
            <div class="pricing-decoration" style="width: 100px; height: 4px; background: linear-gradient(to right, #3498db, #2ecc71); margin: 25px auto;"></div>
            
            <?php if ($isLoggedIn && $trialActive): ?>
            <div class="trial-banner">
                <h3>Your 3-Day Free Trial is Active</h3>
                <p>Your trial will end on <?php echo $trialEndsDate; ?>. Enjoy all premium features during this period.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="pricing-toggle">
            <span class="toggle-option monthly active" data-period="monthly">Monthly</span>
            <label class="toggle-switch">
                <input type="checkbox" id="billing-toggle">
                <span class="toggle-slider"></span>
            </label>
            <span class="toggle-option yearly" data-period="yearly">Yearly <span class="save-badge">Save 20%</span></span>
        </div>
        
        <div class="row">
            <!-- Educator Plan -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="my-0 fw-normal">Educator Plan</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h1 class="card-title pricing-card-title text-center">
                            <span class="price" data-monthly="$18.99" data-yearly="$16.99">$18.99</span>
                            <small class="text-muted fw-light">/seat/month</small>
                        </h1>
                        <p class="text-center text-muted mb-4">For individual teachers who want to save time and give better feedback.</p>
                        <h5>You get:</h5>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Grade hundreds of papers in seconds with One-Click Bulk Grading</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Final score auto-calculated from weighted subscores</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Evidence-based feedback highlighting strengths and weaknesses</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Inline AI comments directly on student submissions</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>GradeGenie creates rubrics, syllabi, and assignment briefs for you</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Email grade reports to students or export as PDF</li>
                        </ul>
                        <button type="button" class="btn btn-lg btn-primary mt-auto w-100" onclick="showPaymentModal('educator')">Start 3-Day Free Trial</button>
                    </div>
                </div>
            </div>
            
            <!-- Institution Plan -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="my-0 fw-normal">Institution Plan</h4>
                        <span class="badge bg-warning">Most Popular</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h1 class="card-title pricing-card-title text-center">
                            <span class="price" data-monthly="$19.99" data-yearly="$17.99">$19.99</span>
                            <small class="text-muted fw-light">/seat/month</small>
                        </h1>
                        <p class="text-center text-muted mb-2">Minimum 3 seats</p>
                        <p class="text-center text-muted mb-4">For teams and schools who need collaboration and shared grading.</p>
                        <h5>Everything in Educator, plus:</h5>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Pre-built rubrics and feedback templates</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Collaborate with your team on assignments</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Role-based access for teachers, TAs, and assistants</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Track grading progress with a team dashboard</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Centralized billing for simplified payments</li>
                        </ul>
                        <button type="button" class="btn btn-lg btn-outline-primary mt-auto w-100" onclick="showContactModal('institution')">Speak to Us</button>
                    </div>
                </div>
            </div>
            
            <!-- Enterprise Plan -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="my-0 fw-normal">Enterprise Plan</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h1 class="card-title pricing-card-title text-center">
                            <span>Custom Pricing</span>
                            <small class="text-muted fw-light d-block">Minimum 3 seats</small>
                        </h1>
                        <p class="text-center text-muted mb-4">For universities and institutions needing advanced tools and support.</p>
                        <h5>Everything in Institution, plus:</h5>
                        <ul class="list-unstyled mt-3 mb-4">
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Plagiarism detection and originality checks</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Student Self-Check Portal for pre-submission feedback</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Support for advanced assignment types and formats</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Custom LMS integrations for seamless workflows</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Advanced analytics and reporting</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Dedicated relationship manager and priority support</li>
                            <li><i class="bi bi-check-circle-fill text-success me-2"></i>Volume-based pricing and centralized admin controls</li>
                        </ul>
                        <button type="button" class="btn btn-lg btn-outline-primary mt-auto w-100" onclick="showContactModal('enterprise')">Speak to Us</button>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="trial-notice">Your account is secure — no charges during your trial</p>
    </div>
    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePaymentModal()">&times;</span>
            <h2>Start Your 3-Day Free Trial</h2>
            <p>Enter your payment details to begin. You won't be charged during your trial period.</p>
            
            <form id="payment-form">
                <input type="hidden" id="selected-plan" value="educator">
                
                <?php if (!$isLoggedIn): ?>
                <div class="form-group">
                    <label for="user-name">Full Name</label>
                    <input type="text" id="user-name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="user-email">Email Address</label>
                    <input type="email" id="user-email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="user-password">Create Password</label>
                    <input type="password" id="user-password" class="form-control" required>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="card-element">Credit or Debit Card</label>
                    <div id="card-element"></div>
                    <div id="card-errors" role="alert"></div>
                </div>
                
                <button id="submit-button" type="submit" class="plan-button">Start Free Trial</button>
            </form>
            
            <p class="trial-notice">Your account is secure — no charges during your trial period</p>
        </div>
    </div>
    
    <!-- Contact Sales Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeContactModal()">&times;</span>
            <h2>Contact Our Sales Team</h2>
            <p>Fill out the form below and our team will get back to you shortly.</p>
            
            <form id="contact-form" onsubmit="submitContactForm(event)">
                <input type="hidden" id="contact-plan" value="enterprise">
                
                <div class="form-group">
                    <label for="contact-name">Full Name</label>
                    <input type="text" id="contact-name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="contact-email">Email Address</label>
                    <input type="email" id="contact-email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="contact-phone">Phone Number</label>
                    <input type="tel" id="contact-phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="contact-company">Institution/Company</label>
                    <input type="text" id="contact-company" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="contact-seats">Estimated Number of Seats</label>
                    <input type="number" id="contact-seats" class="form-control" min="3" value="3" required>
                </div>
                
                <div class="form-group">
                    <label for="contact-message">Additional Information</label>
                    <textarea id="contact-message" class="form-control" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Submit Request</button>
            </form>
            
            <div id="contact-success" style="display: none;">
                <h3 class="text-success">Thank you for your interest!</h3>
                <p>We've received your request and will contact you shortly.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Define variables
        let stripe;
        let elements;
        let cardElement;
        let planType = 'educator';
        let billingPeriod = 'monthly';
        let fromSignup = false;
        
        // Define plan price IDs from Stripe
        const stripePriceIds = {
            educator: {
                month: 'price_1RSXFEAIe95LGsScYUJIPnvs',
                year: 'price_1RSXFEAIe95LGsSccDAdZWfJ'
            },
            institution: {
                month: 'price_1RSXFFAIe95LGsScHKURD9lV',
                year: 'price_1RSXFFAIe95LGsScOUgwLeaT'
            },
            enterprise: {
                month: 'price_1RSXFGAIe95LGsScGwEZIJSN',
                year: 'price_1RSXFGAIe95LGsScGwEZIJSN'
            }
        };
        
        // DOM elements
        const toggleSwitch = document.getElementById('billing-toggle');
        const monthlyPrices = document.querySelectorAll('.monthly-price');
        const yearlyPrices = document.querySelectorAll('.yearly-price');
        const paymentModal = document.getElementById('paymentModal');
        const contactModal = document.getElementById('contactModal');
        
        // Check if user came from signup page
        const fromSignup = new URLSearchParams(window.location.search).get('from') === 'signup';
        let signupData = null;
        
        // If coming from signup, get data from session storage
        if (fromSignup) {
            signupData = {
                name: sessionStorage.getItem('signup_name') || '',
                email: sessionStorage.getItem('signup_email') || '',
                password: sessionStorage.getItem('signup_password') || ''
            };
            console.log('Retrieved signup data from session storage');
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing pricing page');
            
            // Log page initialization
            fetch('api/log-client-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_type: 'pricing_page_init',
                    details: {
                        from_signup: fromSignup,
                        url: window.location.href,
                        has_session_data: !!sessionStorage.getItem('signup_name')
                    }
                })
            }).catch(error => console.error('Error logging event:', error));
            
            // Initialize Stripe
            try {
                stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
                elements = stripe.elements();
                console.log('Stripe initialized successfully');
                
                // Log successful Stripe initialization
                fetch('api/log-client-event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_type: 'stripe_init_success'
                    })
                }).catch(error => console.error('Error logging event:', error));
            } catch (error) {
                console.error('Error initializing Stripe:', error);
                
                // Log Stripe initialization error
                fetch('api/log-client-event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_type: 'stripe_init_error',
                        error: error.message || 'Unknown error',
                        stack: error.stack || 'No stack trace'
                    })
                }).catch(err => console.error('Error logging event:', err));
            }
            
            // Get signup data from session storage if available
            if (fromSignup) {
                signupData = {
                    name: sessionStorage.getItem('signup_name'),
                    email: sessionStorage.getItem('signup_email'),
                    password: sessionStorage.getItem('signup_password')
                };
                
                if (signupData.name && signupData.email && signupData.password) {
                    console.log('User data retrieved from signup page');
                } else {
                    console.warn('Incomplete user data from signup page');
                }
            }
            
            // Set up event listeners
            setupEventListeners();
        });
        
        // Set up all event listeners
        function setupEventListeners() {
            // Toggle between monthly and yearly billing
            toggleSwitch.addEventListener('change', function() {
                if (this.checked) {
                    // Yearly billing
                    monthlyPrices.forEach(el => el.classList.add('hidden'));
                    yearlyPrices.forEach(el => el.classList.remove('hidden'));
                    document.querySelector('.monthly').classList.remove('active');
                    document.querySelector('.yearly').classList.add('active');
                    billingPeriod = 'yearly';
                } else {
                    // Monthly billing
                    yearlyPrices.forEach(el => el.classList.add('hidden'));
                    monthlyPrices.forEach(el => el.classList.remove('hidden'));
                    document.querySelector('.yearly').classList.remove('active');
                    document.querySelector('.monthly').classList.add('active');
                    billingPeriod = 'monthly';
                }
            });
            
            // Click handlers for toggle options
            document.querySelector('.toggle-option.monthly').addEventListener('click', () => {
                toggleSwitch.checked = false;
                toggleSwitch.dispatchEvent(new Event('change'));
            });
            
            document.querySelector('.toggle-option.yearly').addEventListener('click', () => {
                toggleSwitch.checked = true;
                toggleSwitch.dispatchEvent(new Event('change'));
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === paymentModal) {
                    closePaymentModal();
                }
                if (event.target === contactModal) {
                    closeContactModal();
                }
            });
            
            // Handle payment form submission
            const paymentForm = document.getElementById('payment-form');
            if (paymentForm) {
                paymentForm.addEventListener('submit', handlePaymentSubmit);
            }
            
            // Handle contact form submission
            const contactForm = document.getElementById('contact-form');
            if (contactForm) {
                contactForm.addEventListener('submit', submitContactForm);
            }
        }
        
        // Show payment modal
        function showPaymentModal(plan) {
            planType = plan;
            document.getElementById('selected-plan').value = plan;
            
            // Log plan selection
            fetch('api/log-client-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_type: 'plan_selected',
                    plan_type: plan,
                    billing_period: billingPeriod,
                    from_signup: fromSignup
                })
            }).catch(error => console.error('Error logging event:', error));
            
            // Create card element if it doesn't exist yet
            if (!cardElement) {
                try {
                    cardElement = elements.create('card', {
                        style: {
                            base: {
                                fontSize: '16px',
                                color: '#32325d',
                                fontFamily: 'Albert Sans, sans-serif',
                                '::placeholder': {
                                    color: '#aab7c4'
                                }
                            },
                            invalid: {
                                color: '#dc3545',
                                iconColor: '#dc3545'
                            }
                        }
                    });
                    
                    // Mount card element
                    cardElement.mount('#card-element');
                    console.log('Card element mounted successfully');
                    
                    // Log successful card element mounting
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'card_element_mounted',
                            plan_type: plan
                        })
                    }).catch(error => console.error('Error logging event:', error));
                    
                    // Handle validation errors
                    cardElement.addEventListener('change', function(event) {
                        const displayError = document.getElementById('card-errors');
                        if (displayError) {
                            if (event.error) {
                                displayError.textContent = event.error.message;
                                
                                // Log card validation error
                                fetch('api/log-client-event.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        event_type: 'card_validation_error',
                                        error: event.error.message,
                                        code: event.error.code || 'unknown'
                                    })
                                }).catch(error => console.error('Error logging event:', error));
                            } else {
                                displayError.textContent = '';
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error creating card element:', error);
                    
                    // Log card element creation error
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'card_element_error',
                            error: error.message || 'Unknown error',
                            stack: error.stack || 'No stack trace',
                            plan_type: plan
                        })
                    }).catch(err => console.error('Error logging event:', err));
                    
                    return;
                }
            }
            
            // Pre-fill user data if coming from signup page
            if (fromSignup && signupData) {
                const nameInput = document.getElementById('user-name');
                const emailInput = document.getElementById('user-email');
                const passwordInput = document.getElementById('user-password');
                
                if (nameInput && emailInput && passwordInput) {
                    nameInput.value = signupData.name || '';
                    emailInput.value = signupData.email || '';
                    passwordInput.value = signupData.password || '';
                }
            }
            
            // Show modal
            paymentModal.style.display = 'block';
        }
        
        // Close payment modal
        function closePaymentModal() {
            paymentModal.style.display = 'none';
        }
        
        // Show contact modal
        function showContactModal(plan = 'enterprise') {
            document.getElementById('contact-plan').value = plan;
            document.getElementById('contactModal').classList.add('show');
            
            // Log contact modal open event
            fetch('api/log-client-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_type: 'contact_modal_opened',
                    plan_type: plan
                })
            }).catch(error => console.error('Error logging event:', error));
        }
        
        // Close contact modal
        function closeContactModal() {
            document.getElementById('contactModal').classList.remove('show');
        }
        
        // Handle payment form submission
        async function handlePaymentSubmit(event) {
            event.preventDefault();
            
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            
            // Log payment submission attempt
            fetch('api/log-client-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_type: 'payment_attempt',
                    plan_type: planType,
                    billing_period: billingPeriod,
                    from_signup: fromSignup
                })
            }).catch(error => console.error('Error logging event:', error));
            
            try {
                // Get user details if not logged in
                let userData = {};
                if (!<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
                    userData = {
                        name: document.getElementById('user-name').value,
                        email: document.getElementById('user-email').value,
                        password: document.getElementById('user-password').value
                    };
                }
                
                // If we have signup data, use that instead of form data
                if (fromSignup && signupData && signupData.name && signupData.email && signupData.password) {
                    userData = {
                        name: signupData.name,
                        email: signupData.email,
                        password: signupData.password
                    };
                    
                    // Clear session storage after using the data
                    sessionStorage.removeItem('signup_name');
                    sessionStorage.removeItem('signup_email');
                    sessionStorage.removeItem('signup_password');
                }
                
                // Create payment method
                const result = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        email: userData?.email || '<?php echo $userEmail; ?>',
                        name: userData?.name || '<?php echo $userName; ?>'
                    }
                });
                
                if (result.error) {
                    // Show error
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    submitButton.disabled = false;
                    submitButton.textContent = 'Start Free Trial';
                    
                    // Log Stripe payment method creation error
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'stripe_error',
                            error: result.error.message,
                            code: result.error.code,
                            decline_code: result.error.decline_code,
                            param: result.error.param,
                            plan_type: planType,
                            billing_period: billingPeriod
                        })
                    }).catch(err => console.error('Error logging event:', err));
                } else {
                    // Log successful payment method creation
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'payment_method_created',
                            plan_type: planType,
                            billing_period: billingPeriod,
                            payment_method_id: result.paymentMethod.id.substring(0, 8) + '...' // Only log partial ID for security
                        })
                    }).catch(err => console.error('Error logging event:', err));
                    
                    // Process subscription with 3-day trial using new Checkout Session API
                    await processSubscription(result.paymentMethod.id, userData);
                }
            } catch (error) {
                console.error('Error:', error);
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = 'An unexpected error occurred. Please try again.';
                submitButton.disabled = false;
                submitButton.textContent = 'Start Free Trial';
                
                // Log payment error
                fetch('api/log-client-event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_type: 'payment_error',
                        error: error.message || 'Unknown error',
                        stack: error.stack || 'No stack trace',
                        plan_type: planType,
                        billing_period: billingPeriod
                    })
                }).catch(err => console.error('Error logging event:', err));
            }
        }
        
        // Process subscription
        async function processSubscription(paymentMethodId, userData) {
            try {
                // Show loading state
                const submitButton = document.getElementById('submit-button');
                submitButton.disabled = true;
                submitButton.textContent = 'Creating checkout session...';
                
                // Get user ID from session or use temporary ID for new users
                const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'; ?>;
                
                // Determine number of seats (default to 1 for educator plan)
                let seats = 1;
                if (planType === 'institution' || planType === 'enterprise') {
                    seats = 3; // Minimum seats for institution/enterprise plans
                }
                
                // Prepare data for checkout session API
                const checkoutData = {
                    user_id: userId,
                    plan_key: planType,
                    billing_cycle: billingPeriod === 'monthly' ? 'month' : 'year',
                    seats: seats,
                    email: userData?.email || '<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>'
                };
                
                // Log the checkout attempt
                console.log('Creating checkout session with data:', checkoutData);
                
                // Call checkout session API
                const response = await fetch('api/create_checkout_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(checkoutData)
                });
                
                const result = await response.json();
                
                if (result.success && result.url) {
                    // Store user data in session storage if not logged in
                    if (userData) {
                        sessionStorage.setItem('signup_name', userData.name);
                        sessionStorage.setItem('signup_email', userData.email);
                        sessionStorage.setItem('signup_plan', planType);
                        sessionStorage.setItem('signup_billing', billingPeriod);
                    }
                    
                    // Redirect to Stripe Checkout
                    window.location.href = result.url;
                } else {
                    // Show error
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error || 'An error occurred. Please try again.';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Start Free Trial';
                }
            } catch (error) {
                console.error('Error:', error);
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = 'An unexpected error occurred. Please try again.';
                document.getElementById('submit-button').disabled = false;
                document.getElementById('submit-button').textContent = 'Start Free Trial';
            }
        }
        
        // Handle contact form submission
        async function submitContactForm(event) {
            event.preventDefault();
            
            const submitButton = event.target.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            
            const planType = document.getElementById('contact-plan').value;
            const email = document.getElementById('contact-email').value;
            const seats = document.getElementById('contact-seats').value;
            
            // Log contact form submission attempt
            fetch('api/log-client-event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event_type: 'contact_form_submit',
                    plan_type: planType,
                    email: email,
                    seats: seats
                })
            }).catch(error => console.error('Error logging event:', error));
            
            try {
                const response = await fetch('api/contact-sales.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: document.getElementById('contact-name').value,
                        email: email,
                        phone: document.getElementById('contact-phone').value,
                        company: document.getElementById('contact-company').value,
                        seats: seats,
                        message: document.getElementById('contact-message').value,
                        plan: planType
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Log successful submission
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'contact_form_success',
                            plan_type: planType,
                            email: email
                        })
                    }).catch(error => console.error('Error logging event:', error));
                    
                    document.getElementById('contact-form').style.display = 'none';
                    document.getElementById('contact-success').style.display = 'block';
                } else {
                    // Log submission error
                    fetch('api/log-client-event.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            event_type: 'contact_form_error',
                            plan_type: planType,
                            error: data.message || 'Unknown error'
                        })
                    }).catch(error => console.error('Error logging event:', error));
                    
                    alert(data.message || 'An error occurred. Please try again.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Request';
                }
            } catch (error) {
                console.error('Error:', error);
                
                // Log submission exception
                fetch('api/log-client-event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_type: 'contact_form_exception',
                        plan_type: planType,
                        error: error.message || 'Unknown error'
                    })
                }).catch(err => console.error('Error logging event:', err));
                
                alert('An unexpected error occurred. Please try again.');
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Request';
            }
        }
    </script>
</body>
</html>
