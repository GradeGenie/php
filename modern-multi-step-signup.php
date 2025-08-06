<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
try {
    require_once 'api/c.php';
} catch (Exception $e) {
    $error_message = "Database Connection Error: " . $e->getMessage();
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
        header('Location: modern-multi-step-signup.php?step=2');
        exit;
    } elseif (isset($_POST['step']) && $_POST['step'] == '2') {
        // Store step 2 data in session
        $_SESSION['signup_data']['billing_cycle'] = $_POST['billing_cycle'] ?? 'yearly';
        $_SESSION['signup_data']['selected_plan'] = $_POST['selected_plan'] ?? 'educator';
        
        // Redirect to step 3
        header('Location: modern-multi-step-signup.php?step=3');
        exit;
    } elseif (isset($_POST['step']) && $_POST['step'] == '3') {
        // Process the final submission - this would typically include:
        // 1. Creating the user account in the database
        // 2. Processing the payment information with Stripe
        // 3. Setting up the trial subscription
        
        // For demonstration, we'll just store the completion in the session
        $_SESSION['signup_completed'] = true;
        $_SESSION['user_name'] = $_SESSION['signup_data']['name'];
        
        // Redirect to success page
        header('Location: signup-success.php');
        exit;
    }
}

// Get billing cycle and selected plan from session if available
$billing_cycle = $_SESSION['signup_data']['billing_cycle'] ?? 'yearly';
$selected_plan = $_SESSION['signup_data']['selected_plan'] ?? 'institution';

// Get user data from session if available
$user_name = $_SESSION['signup_data']['name'] ?? '';
$user_email = $_SESSION['signup_data']['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <title>Sign Up | GradeGenie</title>
    <?php include 'includes/modern-header.php'; ?>
    
    <!-- Additional styles for icons -->
    <style>
        /* SVG Icon styles */
        .icon-google {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-right: 8px;
        }
        
        .icon-microsoft {
            display: inline-block;
            width: 23px;
            height: 23px;
            margin-right: 8px;
        }
        
        /* Custom styles for the signup form */
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step-indicator-item {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            background-color: hsl(var(--muted));
            color: hsl(var(--muted-foreground));
            font-weight: 500;
        }
        
        .step-indicator-item.active {
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }
        
        .step-indicator-item.completed {
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }
        
        .step-indicator-line {
            flex-grow: 1;
            height: 2px;
            background-color: hsl(var(--muted));
            align-self: center;
            max-width: 3rem;
        }
        
        .step-indicator-line.active {
            background-color: hsl(var(--primary));
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 px-4 py-12">
    <div class="mx-auto w-full max-w-6xl flex justify-center">
        <!-- Signup form card -->
        <div class="w-full max-w-5xl border-0 shadow-2xl rounded-xl bg-white dark:bg-gray-900">
            <!-- Card Header -->
            <div class="space-y-1 pb-8 p-6 border-b">
                <div class="flex justify-center mb-4">
                    <img src="https://app.getgradegenie.com/new/images/logo.png" alt="GradeGenie Logo" class="h-10" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxnIGZpbGw9Im5vbmUiPjxjaXJjbGUgY3g9IjMwIiBjeT0iMzAiIHI9IjI1IiBmaWxsPSIjMDBBRDhFIi8+PGNpcmNsZSBjeD0iNDUiIGN5PSIzMCIgcj0iMjUiIGZpbGw9IiMwMEFEOEUiIGZpbGwtb3BhY2l0eT0iMC43Ii8+PHBhdGggZD0iTTUwIDQwTDMwIDIwTTI1IDQwTDQwIDI1IiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iNCIvPjx0ZXh0IHg9IjgwIiB5PSI0MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjI0IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0iIzMzMyI+R3JhZGUgR2VuaWU8L3RleHQ+PC9nPjwvc3ZnPg=='">
                </div>
                <h1 class="text-center text-3xl font-bold">
                    <?php if ($step === 1): ?>
                        Start Your Effortless Teaching Journey
                    <?php elseif ($step === 2): ?>
                        Transform Your Grading Experience
                    <?php elseif ($step === 3): ?>
                        Complete Your Registration
                    <?php endif; ?>
                </h1>
                <p class="text-center text-lg text-gray-500">
                    <?php if ($step === 1): ?>
                        Join thousands of educators saving 10+ hours per week
                    <?php elseif ($step === 2): ?>
                        Choose the perfect plan for your teaching needs • No credit card required for trial
                    <?php elseif ($step === 3): ?>
                        Your trial begins today — no charges until it ends
                    <?php endif; ?>
                </p>
                
                <!-- Step indicator -->
                <div class="step-indicator mt-6">
                    <div class="step-indicator-item <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                        <?php if ($step > 1): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php else: ?>
                            1
                        <?php endif; ?>
                    </div>
                    <div class="step-indicator-line <?php echo $step > 1 ? 'active' : ''; ?>"></div>
                    <div class="step-indicator-item <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                        <?php if ($step > 2): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?php else: ?>
                            2
                        <?php endif; ?>
                    </div>
                    <div class="step-indicator-line <?php echo $step > 2 ? 'active' : ''; ?>"></div>
                    <div class="step-indicator-item <?php echo $step >= 3 ? 'active' : ''; ?>">
                        3
                    </div>
                </div>
            </div>
            <!-- Step 1: User Information -->  
            <?php if ($step === 1): ?>
                <div class="p-6 space-y-6">
                    <form action="modern-multi-step-signup.php" method="post" class="space-y-4">
                        <input type="hidden" name="step" value="1">
                        
                        <!-- Google Sign-in Button -->
                        <div class="grid grid-cols-1 gap-4">
                            <button type="button" class="w-full h-12 text-base font-medium border border-gray-300 rounded-md flex items-center justify-center bg-white hover:bg-gray-50 transition-colors">
                                <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                Continue with Google
                            </button>
                        </div>

                        <!-- Divider -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">Or continue with email</span>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" placeholder="John Smith" required class="w-full h-11 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Work or .edu email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" placeholder="john@university.edu" required class="w-full h-11 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" id="password" name="password" placeholder="••••••••" required class="w-full h-11 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                <p class="text-xs text-gray-500">Must be at least 8 characters</p>
                            </div>
                        </div>

                        <!-- Free Trial Promo -->
                        <div class="rounded-xl border-2 border-primary/20 bg-gradient-to-r from-primary/5 to-primary/10 p-5">
                            <h3 class="font-semibold mb-3 flex items-center text-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2 text-primary"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                                Start with 30 FREE grading credits
                            </h3>
                            <ul class="space-y-2.5 text-sm">
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-green-500 flex-shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>
                                        <strong>Grade entire classes in minutes</strong> — AI analyzes every submission instantly
                                    </span>
                                </li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-green-500 flex-shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>
                                        <strong>Personalized feedback for each student</strong> — No more repetitive comments
                                    </span>
                                </li>
                                <li class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5 text-green-500 flex-shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>
                                        <strong>Cancel anytime</strong> — No commitment, no risk
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full h-12 text-base font-semibold bg-primary text-white rounded-md hover:bg-primary/90 transition-colors">
                            Start Free Trial →
                        </button>

                        <!-- Terms and Conditions -->
                        <p class="text-xs text-center text-gray-500">
                            By continuing, you agree to our
                            <a href="/terms" class="underline underline-offset-2 hover:text-primary">Terms of Service</a>
                            and
                            <a href="/privacy" class="underline underline-offset-2 hover:text-primary">Privacy Policy</a>
                        </p>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Step 2: Plan Selection -->
            <?php if ($step === 2): ?>
                <div class="p-6 space-y-6">
                    <form action="modern-multi-step-signup.php" method="post" class="space-y-6">
                        <input type="hidden" name="step" value="2">
                        
                        <!-- Money-back guarantee banner -->
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center rounded-full bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 px-4 py-2 text-sm font-medium text-green-800 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>30-Day Money-Back Guarantee • No Setup Fees</span>
                            </div>
                            <p class="text-gray-500">
                                Join 10,000+ educators who've transformed their grading workflow
                            </p>
                        </div>
                        
                        <!-- Billing Cycle Toggle -->
                        <div class="flex justify-center items-center space-x-6 mb-8">
                            <span class="text-base font-medium <?php echo $billing_cycle === 'monthly' ? 'text-gray-900' : 'text-gray-500'; ?>">
                                Monthly
                            </span>
                            <div class="relative">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="billing_cycle" value="yearly" class="sr-only peer" <?php echo $billing_cycle === 'yearly' ? 'checked' : ''; ?>>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                                <?php if ($billing_cycle === 'yearly'): ?>
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-primary to-primary/80 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                        SAVE 2 MONTHS FREE
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-base font-medium <?php echo $billing_cycle === 'yearly' ? 'text-gray-900' : 'text-gray-500'; ?>">
                                Yearly
                            </span>
                        </div>
                        
                        <!-- Plan Selection Cards -->
                        <div class="grid gap-6 lg:grid-cols-3">
                            <?php foreach ($plans[$billing_cycle] as $plan_id => $plan): ?>
                                <div class="relative">
                                    <?php if (isset($plan['popular']) && $plan['popular']): ?>
                                        <div class="absolute -top-4 left-0 right-0 mx-auto w-max rounded-full bg-gradient-to-r from-primary to-primary/80 px-4 py-1.5 text-xs font-bold text-white shadow-lg z-10">
                                            MOST POPULAR
                                        </div>
                                    <?php endif; ?>
                                    
                                    <label for="plan_<?php echo $plan_id; ?>" class="relative flex h-full flex-col rounded-2xl border-2 p-6 transition-all hover:shadow-xl cursor-pointer <?php echo $selected_plan === $plan_id ? 'border-primary bg-gradient-to-b from-primary/5 to-primary/10 shadow-lg scale-105' : (isset($plan['popular']) && $plan['popular'] ? 'border-primary/30 bg-gradient-to-b from-white to-gray-50/20' : 'border-gray-200 bg-white hover:border-gray-300/20'); ?>">
                                        <input type="radio" id="plan_<?php echo $plan_id; ?>" name="selected_plan" value="<?php echo $plan_id; ?>" <?php echo $selected_plan === $plan_id ? 'checked' : ''; ?> class="sr-only">
                                        
                                        <!-- Plan Header -->
                                        <div class="mb-4">
                                            <h3 class="text-xl font-bold mb-2"><?php echo $plan['name']; ?></h3>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-3xl font-bold"><?php echo $plan['price']; ?></span>
                                                <span class="text-gray-500 text-sm"><?php echo $plan['period']; ?></span>
                                            </div>
                                            <?php if ($billing_cycle === 'yearly' && isset($plan['monthlyEquivalent'])): ?>
                                                <div class="text-sm text-primary font-medium mt-1"><?php echo $plan['monthlyEquivalent']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Plan Description -->
                                        <p class="text-sm text-gray-500 mb-4">
                                            <?php if ($plan_id === 'educator'): ?>
                                                Perfect for individual teachers ready to reclaim their time
                                            <?php elseif ($plan_id === 'institution'): ?>
                                                Ideal for departments and small schools
                                            <?php elseif ($plan_id === 'enterprise'): ?>
                                                Complete solution for large institutions
                                            <?php endif; ?>
                                        </p>
                                        
                                        <!-- Features -->
                                        <ul class="space-y-3 text-sm flex-grow">
                                            <?php foreach ($plan['features'] as $index => $feature): ?>
                                                <li class="flex items-start">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4 text-green-500 flex-shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <span class="<?php echo $index === 0 && $plan_id !== 'educator' ? 'font-medium' : ''; ?>">
                                                        <?php echo $feature; ?>
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        
                                        <!-- CTA Button -->
                                        <div class="mt-6">
                                            <?php if ($selected_plan === $plan_id): ?>
                                                <div class="rounded-lg bg-primary text-white p-3 text-center font-medium">
                                                    ✓ Selected
                                                </div>
                                            <?php else: ?>
                                                <div class="rounded-lg bg-gray-100 p-3 text-center text-gray-500 font-medium">
                                                    Select <?php echo $plan['name']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Risk-free Trial Banner -->
                        <div class="rounded-xl border-2 border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 p-5">
                            <div class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 mr-3 mt-0.5 text-emerald-600 flex-shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                <div>
                                    <p class="font-semibold text-emerald-900 mb-1">Start risk-free today</p>
                                    <p class="text-sm text-emerald-700">
                                        Your 3-day trial includes full access to all features. We'll remind you before any charges
                                        apply. Cancel anytime with one click.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Navigation Buttons -->
                        <div class="flex flex-col space-y-3">
                            <button type="submit" class="w-full h-12 text-base font-semibold bg-primary text-white rounded-md hover:bg-primary/90 transition-colors shadow-lg" <?php echo !$selected_plan ? 'disabled' : ''; ?>>
                                Continue to Secure Checkout →
                            </button>
                            <a href="modern-multi-step-signup.php?step=1" class="flex items-center justify-center text-gray-600 hover:text-gray-900 transition-colors py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                Back
                            </a>
                        </div>
                        
                        <!-- Trust Indicators -->
                        <div class="flex items-center justify-center gap-8 pt-4 border-t">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                <span>SSL Encrypted</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                <span>Secure Payment</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>Trusted by 10,000+</span>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Step 3: Payment Information -->  
            <?php if ($step === 3): ?>
                <div class="p-6 space-y-6">
                    <form action="modern-multi-step-signup.php" method="post" class="space-y-6">
                        <input type="hidden" name="step" value="3">
                        
                        <!-- Payment Information Section -->
                        <div class="flex items-center justify-between mb-6">
                            <label for="card" class="text-base font-medium">Payment Information</label>
                            <span class="text-sm font-medium text-emerald-600 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                                Secure — No charges during trial
                            </span>
                        </div>

                        <!-- Card Information Form -->
                        <div class="border-2 rounded-xl p-6 bg-gradient-to-b from-white to-gray-50 space-y-4">
                            <div class="space-y-2">
                                <label for="card-number" class="text-xs text-gray-500 uppercase tracking-wide">Card number</label>
                                <input type="text" id="card-number" placeholder="4242 4242 4242 4242" class="h-11 font-mono w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label for="expiry" class="text-xs text-gray-500 uppercase tracking-wide">Expiry date</label>
                                    <input type="text" id="expiry" placeholder="MM/YY" class="h-11 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                                <div class="space-y-2">
                                    <label for="cvc" class="text-xs text-gray-500 uppercase tracking-wide">CVC</label>
                                    <input type="text" id="cvc" placeholder="123" class="h-11 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Security Message -->
                        <div class="flex items-center space-x-2 text-sm bg-gray-100 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-gray-500 flex-shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                            <span class="text-gray-500">
                                Your payment information is encrypted and secure. We use Stripe for payment processing.
                            </span>
                        </div>

                        <!-- Order Summary -->
                        <div class="rounded-xl border-2 border-primary/20 bg-gradient-to-b from-primary/5 to-primary/10 p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="font-semibold text-lg flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-2 text-primary"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                                    Order Summary
                                </h3>
                                <a href="modern-multi-step-signup.php?step=2" class="text-xs px-3 py-1 bg-white/50 hover:bg-white/80 rounded text-gray-600 hover:text-gray-900 transition-colors">
                                    Change Plan
                                </a>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center pb-3 border-b">
                                    <div>
                                        <div class="font-medium">
                                            <?php echo $plans[$billing_cycle][$selected_plan]['name']; ?> Plan
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo $billing_cycle === 'yearly' ? 'Billed annually' : 'Billed monthly'; ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-lg">
                                            <?php echo $plans[$billing_cycle][$selected_plan]['price']; ?>
                                        </div>
                                        <?php if ($billing_cycle === 'yearly'): ?>
                                            <div class="text-xs text-primary">Save 2 months</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="space-y-2 pt-2">
                                    <h4 class="text-sm font-medium mb-3">Your 3-day trial includes:</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 h-5 w-5 text-green-500 flex-shrink-0"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>
                                                <strong>30 grading credits</strong> to experience the magic
                                            </span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 h-5 w-5 text-green-500 flex-shrink-0"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>
                                                <strong>Full feature access</strong> — no limitations
                                            </span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 h-5 w-5 text-green-500 flex-shrink-0"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span>
                                                <strong>Cancel anytime</strong> before trial ends
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex flex-col space-y-3">
                            <button type="submit" class="w-full h-12 text-base font-semibold bg-primary text-white rounded-md hover:bg-primary/90 transition-colors shadow-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-5 w-5"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                Start Your Free Trial
                            </button>

                            <a href="modern-multi-step-signup.php?step=2" class="flex items-center justify-center text-gray-600 hover:text-gray-900 transition-colors py-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                Back to plans
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Card Footer -->
            <div class="p-6 flex flex-col space-y-4 border-t">
                <div class="text-center text-sm">
                    Already have an account?
                    <a href="login.php" class="font-semibold text-primary hover:underline">
                        Log in
                    </a>
                </div>
                <div class="flex items-center justify-center text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 h-3 w-3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    256-bit SSL encryption
                </div>
            </div>
        </div>
    </div>
</body>
</html>
