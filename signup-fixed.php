<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and Stripe
try {
    require_once 'api/c.php';
    require_once 'vendor/autoload.php';
    
    // Set Stripe API key
    \Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY'); // Replace with your actual secret key in production
} catch (Exception $e) {
    $error_message = "Connection Error: " . $e->getMessage();
}

// Initialize step (default to 1)
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Define pricing plans
$plans = [
    'monthly' => [
        'educator' => [
            'name' => 'Educator',
            'price' => '$18.99',
            'period' => '/seat/month',
            'yearlyPrice' => '$16.99',
            'yearlyPeriod' => '/seat/month, billed yearly',
            'price_id' => 'price_1OdTRxAIe95LGsScBFsYOHrm',
            'features' => [
                'Grade hundreds of papers in seconds with One-Click Bulk Grading',
                'Final score auto-calculated from weighted subscores',
                'Evidence-based feedback highlighting strengths and weaknesses',
                'Inline AI comments directly on student submissions',
                'GradeGenie creates rubrics, syllabi, and assignment briefs for you',
                'Email grade reports to students or export as PDF',
            ],
            'description' => 'Perfect for individual teachers ready to reclaim their time',
        ],
        'institution' => [
            'name' => 'Institution',
            'price' => '$19.99',
            'period' => '/seat/month (min 3 seats)',
            'yearlyPrice' => '$17.99',
            'yearlyPeriod' => '/seat/month (min 3 seats), billed yearly',
            'popular' => true,
            'price_id' => 'price_1OdTSNAIe95LGsSc9FqYP8Qz',
            'features' => [
                'Everything in Educator plan, plus:',
                'Pre-built rubrics and feedback templates for consistent grading',
                'Collaborate with your team on assignments',
                'Role-based access for teachers, TAs, and assistants',
                'Track grading progress with a team dashboard',
                'Centralized billing for simplified payments',
            ],
            'description' => 'Ideal for departments and small schools',
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 'Custom',
            'period' => ' Pricing (min 3 seats)',
            'yearlyPrice' => 'Custom',
            'yearlyPeriod' => ' Pricing (min 3 seats)',
            'features' => [
                'Everything in Institution plan, plus:',
                'Plagiarism detection and originality checks',
                'Student Self-Check Portal for pre-submission feedback',
                'Support for advanced assignment types and formats',
                'Custom LMS integrations for seamless workflows',
                'Advanced analytics and reporting for educators and admins',
                'Dedicated relationship manager and priority support',
            ],
            'description' => 'Complete solution for large institutions',
        ],
    ],
    'yearly' => [
        'educator' => [
            'name' => 'Educator',
            'price' => '$16.99',
            'period' => '/seat/month, billed yearly',
            'monthlyEquivalent' => 'Save compared to monthly',
            'price_id' => 'price_1OdTSNAIe95LGsSc9FqYP8Qz',
            'features' => [
                'Grade hundreds of papers in seconds with One-Click Bulk Grading',
                'Final score auto-calculated from weighted subscores',
                'Evidence-based feedback highlighting strengths and weaknesses',
                'Inline AI comments directly on student submissions',
                'GradeGenie creates rubrics, syllabi, and assignment briefs for you',
                'Email grade reports to students or export as PDF',
            ],
            'description' => 'Perfect for individual teachers ready to reclaim their time',
        ],
        'institution' => [
            'name' => 'Institution',
            'price' => '$17.99',
            'period' => '/seat/month (min 3 seats), billed yearly',
            'monthlyEquivalent' => 'Save compared to monthly',
            'popular' => true,
            'price_id' => 'price_1OdTSNAIe95LGsSc9FqYP8Qz',
            'features' => [
                'Everything in Educator plan, plus:',
                'Pre-built rubrics and feedback templates for consistent grading',
                'Collaborate with your team on assignments',
                'Role-based access for teachers, TAs, and assistants',
                'Track grading progress with a team dashboard',
                'Centralized billing for simplified payments',
            ],
            'description' => 'Ideal for departments and small schools',
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 'Custom',
            'period' => ' Pricing (min 3 seats)',
            'monthlyEquivalent' => 'Contact us for details',
            'features' => [
                'Everything in Institution plan, plus:',
                'Plagiarism detection and originality checks',
                'Student Self-Check Portal for pre-submission feedback',
                'Support for advanced assignment types and formats',
                'Custom LMS integrations for seamless workflows',
                'Advanced analytics and reporting for educators and admins',
                'Dedicated relationship manager and priority support',
            ],
            'description' => 'Complete solution for large institutions',
        ],
    ],
];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        // Store step 1 data in session
        $_SESSION['signup_data'] = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
        ];
        
        // Redirect to step 2
        header('Location: signup-fixed.php?step=2');
        exit;
    } elseif (isset($_POST['step']) && $_POST['step'] == '2') {
        // Store step 2 data in session
        $_SESSION['signup_data']['billing_cycle'] = $_POST['billing_cycle'] ?? 'yearly';
        $_SESSION['signup_data']['selected_plan'] = $_POST['selected_plan'] ?? 'institution';
        
        // Redirect to step 3
        header('Location: signup-fixed.php?step=3');
        exit;
    } elseif (isset($_POST['step']) && $_POST['step'] == '3') {
        // Process the final submission with Stripe integration
        try {
            // Get the selected plan and billing cycle
            $billing_cycle = $_SESSION['signup_data']['billing_cycle'] ?? 'yearly';
            $selected_plan = $_SESSION['signup_data']['selected_plan'] ?? 'institution';
            $price_id = $plans[$billing_cycle][$selected_plan]['price_id'];
            
            // Get card details from form
            $card_number = $_POST['card_number'] ?? '';
            $card_expiry = $_POST['card_expiry'] ?? '';
            $card_cvc = $_POST['card_cvc'] ?? '';
            
            // Parse expiry date
            $expiry_parts = explode('/', $card_expiry);
            $exp_month = isset($expiry_parts[0]) ? trim($expiry_parts[0]) : '';
            $exp_year = isset($expiry_parts[1]) ? trim($expiry_parts[1]) : '';
            
            // Add 20 to year if it's a 2-digit year
            if (strlen($exp_year) == 2) {
                $exp_year = '20' . $exp_year;
            }
            
            // Create a token (Note: This approach is legacy. In production, use Elements or Checkout)
            $token = \Stripe\Token::create([
                'card' => [
                    'number' => $card_number,
                    'exp_month' => $exp_month,
                    'exp_year' => $exp_year,
                    'cvc' => $card_cvc,
                ],
            ]);
            
            // Create a customer in Stripe
            $customer = \Stripe\Customer::create([
                'email' => $_SESSION['signup_data']['email'],
                'name' => $_SESSION['signup_data']['name'],
                'source' => $token->id, // Attach the token as the payment source
            ]);
            
            // Create a subscription with a 3-day trial
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [[
                    'price' => $price_id,
                ]],
                'trial_period_days' => 3, // 3-day free trial
            ]);
            
            // Calculate trial end date
            $trial_ends_at = date('Y-m-d H:i:s', strtotime('+3 days'));
            
            // Store user data in database (simplified for example)
            // In a real application, you would use prepared statements
            $hashed_password = password_hash($_SESSION['signup_data']['password'], PASSWORD_DEFAULT);
            
            // Redirect to success page
            $_SESSION['signup_success'] = true;
            header('Location: dashboard.php');
            exit;
            
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

// Get session data for form values
$name = $_SESSION['signup_data']['name'] ?? '';
$email = $_SESSION['signup_data']['email'] ?? '';
$billing_cycle = $_SESSION['signup_data']['billing_cycle'] ?? 'yearly';
$selected_plan = $_SESSION['signup_data']['selected_plan'] ?? 'institution';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - GradeGenie</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        
        .bg-gradient-custom {
            background: linear-gradient(135deg, #f0f9ff 0%, #e6f7ff 100%);
        }
        
        .progress-step {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .progress-step.active {
            background-color: #00AD8E;
            color: white;
        }
        
        .progress-step.completed {
            background-color: #00AD8E;
            color: white;
        }
        
        .progress-line {
            flex-grow: 1;
            height: 2px;
            background-color: #e5e7eb;
        }
        
        .progress-line.active {
            background-color: #00AD8E;
        }
        
        .plan-card {
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .plan-card:hover {
            border-color: #00AD8E;
        }
        
        .plan-card.selected {
            border-color: #00AD8E;
            background-color: rgba(0, 173, 142, 0.05);
        }
        
        .card-input {
            height: 2.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .card-input:focus {
            outline: none;
            border-color: #00AD8E;
            box-shadow: 0 0 0 2px rgba(0, 173, 142, 0.2);
        }
        
        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            background-color: #f9fafb;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.15s ease;
            padding: 0.625rem 1.25rem;
        }
        
        .btn-lg {
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #00AD8E;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: rgba(0, 173, 142, 0.9);
        }
        
        .btn-outline {
            border: 1px solid #d1d5db;
            color: #4b5563;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
        }
        
        .btn-ghost {
            background-color: transparent;
            color: #6b7280;
        }
        
        .btn-ghost:hover {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .toggle-bg {
            background-color: #e5e7eb;
            border-radius: 9999px;
            cursor: pointer;
            height: 1.5rem;
            position: relative;
            transition: background-color 0.2s ease;
            width: 3rem;
        }
        
        .toggle-bg.active {
            background-color: #00AD8E;
        }
        
        .toggle-dot {
            background-color: white;
            border-radius: 9999px;
            height: 1.25rem;
            left: 0.125rem;
            position: absolute;
            top: 0.125rem;
            transition: transform 0.2s ease;
            width: 1.25rem;
        }
        
        .toggle-bg.active .toggle-dot {
            transform: translateX(1.5rem);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-custom font-inter antialiased">
    <!-- Main container -->
    <div class="flex min-h-screen px-4 py-12">
        <div class="mx-auto w-full max-w-6xl flex justify-center">
            <!-- Signup card -->
            <div class="w-full max-w-5xl border-0 shadow-2xl bg-white rounded-xl overflow-hidden">
                <!-- Card header -->
                <div class="space-y-1 pb-8 pt-8 px-8">
                    <div class="flex justify-center mb-4">
                        <a href="index.php">
                            <img src="https://app.getgradegenie.com/new/images/logo.png" alt="GradeGenie Logo" class="h-12" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxnIGZpbGw9Im5vbmUiPjxjaXJjbGUgY3g9IjMwIiBjeT0iMzAiIHI9IjI1IiBmaWxsPSIjMDBBRDhFIi8+PGNpcmNsZSBjeD0iNDUiIGN5PSIzMCIgcj0iMjUiIGZpbGw9IiMwMEFEOEUiIGZpbGwtb3BhY2l0eT0iMC43Ii8+PHBhdGggZD0iTTUwIDQwTDMwIDIwTTI1IDQwTDQwIDI1IiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iNCIvPjx0ZXh0IHg9IjgwIiB5PSI0MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjI0IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0iIzMzMyI+R3JhZGUgR2VuaWU8L3RleHQ+PC9nPjwvc3ZnPg=='">
                        </a>
                    </div>
                    <h1 class="text-center text-3xl font-bold card-title">
                        <?php if ($step === 1): ?>
                            Start Your Effortless Teaching Journey
                        <?php elseif ($step === 2): ?>
                            Transform Your Grading Experience
                        <?php elseif ($step === 3): ?>
                            Complete Your Registration
                        <?php endif; ?>
                    </h1>
                    <p class="text-center text-lg text-gray-600 card-description">
                        <?php if ($step === 1): ?>
                            Join thousands of educators saving 10+ hours per week
                        <?php elseif ($step === 2): ?>
                            Choose the perfect plan for your teaching needs • No credit card required for trial
                        <?php elseif ($step === 3): ?>
                            Your trial begins today — no charges until it ends
                        <?php endif; ?>
                    </p>
                </div>
                
                <!-- Progress bar -->
                <div class="px-8 pb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="progress-step <?php echo $step >= 1 ? 'active' : ''; ?>">
                                <?php if ($step > 1): ?>
                                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                <?php else: ?>
                                    1
                                <?php endif; ?>
                            </div>
                            <div class="progress-line <?php echo $step >= 2 ? 'active' : ''; ?>"></div>
                            <div class="progress-step <?php echo $step >= 2 ? 'active' : ''; ?>">
                                <?php if ($step > 2): ?>
                                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                <?php else: ?>
                                    2
                                <?php endif; ?>
                            </div>
                            <div class="progress-line <?php echo $step >= 3 ? 'active' : ''; ?>"></div>
                            <div class="progress-step <?php echo $step >= 3 ? 'active' : ''; ?>">
                                3
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            Step <?php echo $step; ?> of 3
                        </div>
                    </div>
                </div>
                
                <!-- Card content -->
                <div class="px-8 pb-8 space-y-6">
                    <?php if (isset($error_message)): ?>
                        <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 text-red-700">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Step 1: Account creation -->
                    <?php if ($step == 1): ?>
                        <form method="post" class="space-y-6">
                            <input type="hidden" name="step" value="1">
                            
                            <div class="grid grid-cols-1 gap-4">
                                <button type="button" class="btn btn-outline w-full h-12 text-base font-medium">
                                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Continue with Google
                                </button>
                            </div>

                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <span class="w-full border-t border-gray-200"></span>
                                </div>
                                <div class="relative flex justify-center text-xs uppercase">
                                    <span class="bg-white px-2 text-gray-500">Or continue with email</span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" class="card-input" required>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="card-input" required>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" id="password" name="password" class="card-input" required>
                                    <p class="text-xs text-gray-500">Must be at least 8 characters with a number and special character</p>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-full font-semibold">
                                    Continue to Select Your Plan
                                </button>
                                <p class="text-center text-sm text-gray-500 mt-4">
                                    Already have an account? <a href="login.php" class="text-[#00AD8E] hover:underline">Sign in</a>
                                </p>
                            </div>
                        </form>
                    
                    <!-- Step 2: Plan selection -->
                    <?php elseif ($step == 2): ?>
                        <form method="post" class="space-y-6">
                            <input type="hidden" name="step" value="2">
                            
                            <!-- Billing toggle -->
                            <div class="flex justify-center items-center space-x-4 py-2">
                                <span class="text-sm font-medium <?php echo $billing_cycle === 'monthly' ? 'text-gray-900' : 'text-gray-500'; ?>">Monthly</span>
                                
                                <label class="relative inline-block">
                                    <input type="checkbox" name="billing_toggle" class="sr-only" <?php echo $billing_cycle === 'yearly' ? 'checked' : ''; ?>>
                                    <div class="toggle-bg <?php echo $billing_cycle === 'yearly' ? 'active' : ''; ?>">
                                        <div class="toggle-dot"></div>
                                    </div>
                                </label>
                                
                                <div class="flex items-center">
                                    <span class="text-sm font-medium <?php echo $billing_cycle === 'yearly' ? 'text-gray-900' : 'text-gray-500'; ?>">Annual</span>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Save 15%</span>
                                </div>
                                
                                <input type="hidden" name="billing_cycle" value="<?php echo $billing_cycle; ?>" id="billing_cycle_input">
                            </div>
                            
                            <!-- Plan cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                                <?php foreach (['educator', 'institution', 'enterprise'] as $plan_key): ?>
                                    <?php $plan = $plans[$billing_cycle][$plan_key]; ?>
                                    <div class="relative">
                                        <?php if (isset($plan['popular']) && $plan['popular']): ?>
                                            <div class="absolute -top-3 left-0 right-0 flex justify-center">
                                                <span class="px-3 py-1 text-xs font-semibold text-[#00AD8E] bg-[#E6F7F4] rounded-full shadow-sm">
                                                    Most Popular
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <label class="plan-card <?php echo $selected_plan === $plan_key ? 'selected' : ''; ?>">
                                            <input type="radio" name="selected_plan" value="<?php echo $plan_key; ?>" class="sr-only" <?php echo $selected_plan === $plan_key ? 'checked' : ''; ?>>
                                            
                                            <div class="flex justify-between items-start mb-4">
                                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                                <?php if ($selected_plan === $plan_key): ?>
                                                    <svg class="h-5 w-5 text-[#00AD8E]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <div class="flex items-baseline">
                                                    <span class="text-2xl font-bold"><?php echo htmlspecialchars($plan['price']); ?></span>
                                                    <span class="ml-1 text-sm text-gray-500"><?php echo htmlspecialchars($plan['period']); ?></span>
                                                </div>
                                                <?php if ($billing_cycle === 'yearly' && isset($plan['monthlyEquivalent'])): ?>
                                                    <p class="text-xs text-[#00AD8E] mt-1"><?php echo htmlspecialchars($plan['monthlyEquivalent']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($plan['description']); ?></p>
                                            
                                            <div class="space-y-2">
                                                <?php foreach ($plan['features'] as $feature): ?>
                                                    <div class="flex items-start">
                                                        <svg class="h-5 w-5 text-[#00AD8E] mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="text-sm"><?php echo htmlspecialchars($feature); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="pt-6 flex flex-col space-y-3">
                                <button type="submit" class="btn btn-primary btn-lg font-semibold">
                                    Continue to Payment
                                </button>
                                <a href="signup-fixed.php?step=1" class="btn btn-ghost">
                                    Back to Account Details
                                </a>
                            </div>
                        </form>
                    
                    <!-- Step 3: Payment information -->
                    <?php elseif ($step == 3): ?>
                        <form method="post" class="space-y-6">
                            <input type="hidden" name="step" value="3">
                            
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-base font-medium">Payment Information</h3>
                                <span class="text-sm font-medium text-emerald-600 flex items-center">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                    </svg>
                                    Secure — No charges during trial
                                </span>
                            </div>

                            <!-- Card Information Form -->
                            <div class="border-2 rounded-xl p-6 bg-gradient-to-b from-white to-gray-50 space-y-4">
                                <div class="space-y-2">
                                    <label for="card_number" class="text-xs text-gray-500 uppercase tracking-wide">Card number</label>
                                    <input type="text" id="card_number" name="card_number" placeholder="4242 4242 4242 4242" class="card-input font-mono" required>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label for="card_expiry" class="text-xs text-gray-500 uppercase tracking-wide">Expiry date</label>
                                        <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY" class="card-input" required>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="card_cvc" class="text-xs text-gray-500 uppercase tracking-wide">CVC</label>
                                        <input type="text" id="card_cvc" name="card_cvc" placeholder="123" class="card-input" required>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2 text-sm bg-gray-100 p-3 rounded-lg">
                                <svg class="h-4 w-4 text-gray-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                </svg>
                                <span class="text-gray-600">
                                    Your payment information is encrypted and secure. We use Stripe for payment processing.
                                </span>
                            </div>

                            <!-- Order Summary -->
                            <?php $plan = $plans[$billing_cycle][$selected_plan]; ?>
                            <div class="summary-card">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="font-semibold text-lg flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-[#00AD8E]" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                                            <path d="M5 3v4"/>
                                            <path d="M19 17v4"/>
                                            <path d="M3 5h4"/>
                                            <path d="M17 19h4"/>
                                        </svg>
                                        Order Summary
                                    </h3>
                                    <a href="signup-fixed.php?step=2" class="btn btn-ghost h-8 text-xs py-1 px-2">
                                        Change Plan
                                    </a>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                        <div>
                                            <div class="font-medium">
                                                <?php echo htmlspecialchars($plan['name']); ?> Plan
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <?php echo $billing_cycle === 'yearly' ? 'Billed annually' : 'Billed monthly'; ?>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold text-lg">
                                                <?php echo htmlspecialchars($plan['price']); ?>
                                            </div>
                                            <?php if ($billing_cycle === 'yearly'): ?>
                                                <div class="text-xs text-[#00AD8E]">Save 2 months</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="space-y-2 pt-2">
                                        <h4 class="text-sm font-medium mb-3">Your 3-day trial includes:</h4>
                                        <div class="space-y-2">
                                            <div class="flex items-center text-sm">
                                                <svg class="mr-3 h-5 w-5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                <span>
                                                    <strong>30 grading credits</strong> to experience the magic
                                                </span>
                                            </div>
                                            <div class="flex items-center text-sm">
                                                <svg class="mr-3 h-5 w-5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                <span>
                                                    <strong>Full feature access</strong> — no limitations
                                                </span>
                                            </div>
                                            <div class="flex items-center text-sm">
                                                <svg class="mr-3 h-5 w-5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                <span>
                                                    <strong>Cancel anytime</strong> before trial ends
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mt-4">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-[#00AD8E] border-gray-300 rounded focus:ring-[#00AD8E]" required>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="terms" class="text-gray-600">I agree to the <a href="#" class="text-[#00AD8E] hover:underline">Terms of Service</a> and <a href="#" class="text-[#00AD8E] hover:underline">Privacy Policy</a></label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col space-y-3">
                                <button type="submit" class="btn btn-primary btn-lg font-semibold shadow-lg">
                                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="14" x="2" y="5" rx="2"/>
                                        <line x1="2" x2="22" y1="10" y2="10"/>
                                    </svg>
                                    Start Your Free Trial
                                </button>

                                <a href="signup-fixed.php?step=2" class="btn btn-ghost flex justify-center items-center">
                                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m12 19-7-7 7-7"/>
                                        <path d="M19 12H5"/>
                                    </svg>
                                    Back to plans
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle billing cycle
        document.addEventListener('DOMContentLoaded', function() {
            const billingToggle = document.querySelector('input[name="billing_toggle"]');
            const billingCycleInput = document.getElementById('billing_cycle_input');
            
            if (billingToggle && billingCycleInput) {
                billingToggle.addEventListener('change', function() {
                    const isYearly = this.checked;
                    billingCycleInput.value = isYearly ? 'yearly' : 'monthly';
                    
                    // Toggle active class for styling
                    const toggleBg = this.parentElement.querySelector('.toggle-bg');
                    if (toggleBg) {
                        if (isYearly) {
                            toggleBg.classList.add('active');
                        } else {
                            toggleBg.classList.remove('active');
                        }
                    }
                    
                    // Submit the form to update the view
                    this.form.submit();
                });
            }
            
            // Plan selection
            const planRadios = document.querySelectorAll('input[name="selected_plan"]');
            planRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove selected class from all plan cards
                    document.querySelectorAll('.plan-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    
                    // Add selected class to the selected plan card
                    this.closest('.plan-card').classList.add('selected');
                });
            });
            
            // Format card inputs
            const cardNumber = document.getElementById('card_number');
            if (cardNumber) {
                cardNumber.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    let formattedValue = '';
                    
                    for (let i = 0; i < value.length; i++) {
                        if (i > 0 && i % 4 === 0) {
                            formattedValue += ' ';
                        }
                        formattedValue += value[i];
                    }
                    
                    e.target.value = formattedValue;
                });
            }
            
            const cardExpiry = document.getElementById('card_expiry');
            if (cardExpiry) {
                cardExpiry.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    
                    if (value.length > 2) {
                        e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    } else {
                        e.target.value = value;
                    }
                });
            }
            
            const cardCvc = document.getElementById('card_cvc');
            if (cardCvc) {
                cardCvc.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
                });
            }
        });
    </script>
</body>
</html>