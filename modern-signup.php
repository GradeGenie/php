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

// Include our Tailwind configuration
require_once 'tailwind-config.php';

// Define pricing plans
$plans = [
    'educator' => [
        'name' => 'Educator Plan',
        'monthly_price' => 18.99,
        'yearly_price' => 16.99,
        'monthly_price_id' => 'price_1OsYTgAIe95LGsSc6Nh3Ck0r',
        'yearly_price_id' => 'price_1OsYTgAIe95LGsSc6Nh3Ck0r', // Update with actual yearly price ID
        'description' => 'For individual teachers who want to save time and give better feedback.',
        'features' => [
            'Grade hundreds of papers in seconds with One-Click Bulk Grading',
            'Final score auto-calculated from weighted subscores',
            'Evidence-based feedback highlighting strengths and weaknesses',
            'Inline AI comments directly on student submissions',
            'GradeGenie creates rubrics, syllabi, and assignment briefs',
            'Email grade reports to students or export as PDF'
        ],
        'cta' => 'Start 3-Day Free Trial',
        'contact_required' => false
    ],
    'institution' => [
        'name' => 'Institution Plan',
        'monthly_price' => 19.99,
        'yearly_price' => 17.99,
        'description' => 'For teams and schools who need collaboration and shared grading.',
        'min_seats' => 3,
        'features' => [
            'Pre-built rubrics and feedback templates for consistent grading',
            'Collaborate with your team on assignments',
            'Role-based access for teachers, TAs, and assistants',
            'Track grading progress with a team dashboard',
            'Centralized billing for simplified payments'
        ],
        'cta' => 'Speak to Us',
        'contact_required' => true
    ],
    'enterprise' => [
        'name' => 'Enterprise Plan',
        'price' => 'Custom Pricing',
        'description' => 'For universities and institutions needing advanced tools and support.',
        'min_seats' => 3,
        'features' => [
            'Plagiarism detection and originality checks',
            'Student Self-Check Portal for pre-submission feedback',
            'Support for advanced assignment types and formats',
            'Custom LMS integrations for seamless workflows',
            'Advanced analytics and reporting for educators and admins',
            'Dedicated relationship manager and priority support',
            'Volume-based pricing and centralized admin controls'
        ],
        'cta' => 'Speak to Us',
        'contact_required' => true
    ]
];
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GradeGenie - AI-Powered Grading Assistant</title>
    
    <!-- Include Tailwind CSS and fonts -->
    <?php include_tailwind_styles(); ?>
    
    <!-- Add brand colors CSS -->
    <link rel="stylesheet" href="styles/brand-colors.css">
    
    <!-- Add Tailwind CDN for development (remove in production and use a build process) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configure Tailwind with our theme -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        onest: ['var(--font-onest)'],
                        inter: ['var(--font-inter)'],
                        sans: ['var(--font-inter)']
                    },
                    colors: {
                        border: 'hsl(var(--border))',
                        input: 'hsl(var(--input))',
                        ring: 'hsl(var(--ring))',
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                        primary: {
                            DEFAULT: 'hsl(var(--primary))',
                            foreground: 'hsl(var(--primary-foreground))'
                        },
                        secondary: {
                            DEFAULT: 'hsl(var(--secondary))',
                            foreground: 'hsl(var(--secondary-foreground))'
                        },
                        mint: {
                            DEFAULT: 'hsl(var(--mint))',
                            foreground: 'hsl(var(--mint-foreground))'
                        },
                        notebook: {
                            DEFAULT: 'hsl(var(--notebook))',
                            foreground: 'hsl(var(--notebook-foreground))'
                        },
                        navy: {
                            DEFAULT: 'hsl(var(--navy))',
                            foreground: 'hsl(var(--navy-foreground))'
                        },
                        destructive: {
                            DEFAULT: 'hsl(var(--destructive))',
                            foreground: 'hsl(var(--destructive-foreground))'
                        },
                        muted: {
                            DEFAULT: 'hsl(var(--muted))',
                            foreground: 'hsl(var(--muted-foreground))'
                        },
                        accent: {
                            DEFAULT: 'hsl(var(--accent))',
                            foreground: 'hsl(var(--accent-foreground))'
                        },
                        popover: {
                            DEFAULT: 'hsl(var(--popover))',
                            foreground: 'hsl(var(--popover-foreground))'
                        },
                        card: {
                            DEFAULT: 'hsl(var(--card))',
                            foreground: 'hsl(var(--card-foreground))'
                        }
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-white font-inter antialiased">
    <!-- Include dark mode script -->
    <?php include_dark_mode_script(); ?>

    <!-- Main container -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <!-- Logo and Navigation -->
        <nav class="flex items-center justify-between py-6">
            <div class="flex items-center">
                <img src="https://app.getgradegenie.com/new/images/logo.png" alt="GradeGenie Logo" class="h-10" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNDAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCAyNDAgNjAiPjxnIGZpbGw9Im5vbmUiPjxjaXJjbGUgY3g9IjMwIiBjeT0iMzAiIHI9IjI1IiBmaWxsPSIjMDBBRDhFIi8+PGNpcmNsZSBjeD0iNDUiIGN5PSIzMCIgcj0iMjUiIGZpbGw9IiMwMEFEOEUiIGZpbGwtb3BhY2l0eT0iMC43Ii8+PHBhdGggZD0iTTUwIDQwTDMwIDIwTTI1IDQwTDQwIDI1IiBzdHJva2U9IiNmZmYiIHN0cm9rZS13aWR0aD0iNCIvPjx0ZXh0IHg9IjgwIiB5PSI0MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjI0IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0iIzMzMyI+R3JhZGUgR2VuaWU8L3RleHQ+PC9nPjwvc3ZnPg=='">
            </div>
            <div>
                <a href="login.php" class="text-sm font-medium text-foreground hover:text-primary">Log in</a>
            </div>
        </nav>
        
        <!-- Header -->
        <header class="text-center mb-12 mt-10">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl font-onest mb-4 text-gray-800">
                Join 30,000+ Educators Saving Time with GradeGenie
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Grading Made Effortless. Feedback Students Love.
            </p>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto mt-2">
                Save hours every week with AI-powered grading that's fast, fair, and transparent.
            </p>
        </header>

        <!-- Pricing toggle -->
        <div class="flex items-center justify-center mb-10">
            <span class="text-sm font-medium mr-3">Monthly</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="billing-toggle" class="sr-only peer">
                <div class="w-11 h-6 bg-muted peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
            <span class="text-sm font-medium ml-3">Annual <span class="text-xs text-primary font-semibold">Save 10%</span></span>
        </div>

        <!-- Pricing cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            <?php foreach ($plans as $plan_id => $plan): ?>
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 shadow-sm hover:shadow-md transition-all overflow-hidden">
                    <!-- Plan header -->
                    <div class="p-6 border-b border-gray-800">
                        <h3 class="text-xl font-bold font-onest text-white"><?php echo $plan['name']; ?></h3>
                        <div class="mt-3">
                            <?php if (isset($plan['monthly_price'])): ?>
                                <div class="pricing-monthly">
                                    <span class="text-3xl font-bold text-white">$<?php echo $plan['monthly_price']; ?></span>
                                    <span class="text-gray-400">/month per seat</span>
                                </div>
                                <div class="pricing-yearly hidden">
                                    <span class="text-3xl font-bold text-white">$<?php echo $plan['yearly_price']; ?></span>
                                    <span class="text-gray-400">/month per seat</span>
                                    <p class="text-sm text-gray-400">Billed annually</p>
                                </div>
                            <?php else: ?>
                                <span class="text-2xl font-bold text-white"><?php echo $plan['price']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-3 text-sm text-gray-300"><?php echo $plan['description']; ?></p>
                        <?php if (isset($plan['min_seats'])): ?>
                            <p class="mt-1 text-xs text-gray-400">Minimum <?php echo $plan['min_seats']; ?> seats</p>
                        <?php endif; ?>
                    </div>

                    <!-- Plan features -->
                    <div class="p-6">
                        <h4 class="text-sm font-medium mb-4 text-gray-200">
                            <?php echo ($plan_id === 'educator') ? 'You get:' : 'Everything in ' . ($plan_id === 'institution' ? 'Educator' : 'Institution') . ', plus:'; ?>
                        </h4>
                        <ul class="space-y-3">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li class="flex">
                                    <svg class="h-5 w-5 flex-shrink-0 text-[#00AD8E]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-3 text-sm text-gray-300"><?php echo $feature; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Plan CTA -->
                    <div class="p-6 mt-auto">
                        <?php if ($plan['contact_required']): ?>
                            <a href="contact.php?plan=<?php echo $plan_id; ?>" class="block w-full py-3 px-4 rounded-md bg-secondary text-secondary-foreground font-medium text-center hover:bg-secondary/90 transition-colors">
                                <?php echo $plan['cta']; ?>
                            </a>
                        <?php else: ?>
                            <button onclick="startFreeTrial('<?php echo $plan_id; ?>')" class="block w-full py-3 px-4 rounded-md bg-primary text-primary-foreground font-medium text-center hover:bg-primary/90 transition-colors">
                                <?php echo $plan['cta']; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Signup form -->
        <div id="signup-form-container" class="max-w-md mx-auto bg-[#1a1a1a] rounded-lg border border-gray-800 shadow-sm p-6 hidden">
            <h2 class="text-2xl font-bold font-onest mb-6 text-white">Create your account</h2>
            <div id="form-message" class="mb-6 p-4 rounded-md bg-[#00AD8E]/20 border border-[#00AD8E] text-white hidden"></div>
            
            <form id="signup-form" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium mb-1 text-gray-200">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-700 bg-[#222] text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#00AD8E]" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium mb-1 text-gray-200">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-700 bg-[#222] text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#00AD8E]" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1 text-gray-200">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-700 bg-[#222] text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#00AD8E]" required>
                </div>
                <div>
                    <label for="confirm-password" class="block text-sm font-medium mb-1 text-gray-200">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" class="w-full px-3 py-2 border border-gray-700 bg-[#222] text-white rounded-md focus:outline-none focus:ring-2 focus:ring-[#00AD8E]" required>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 rounded-md bg-[#00AD8E] text-white font-medium text-center hover:bg-[#00AD8E]/90 transition-colors">
                        Start Your 3-Day Free Trial
                    </button>
                </div>
                <p class="text-xs text-gray-400 text-center mt-4">
                    By signing up, you agree to our <a href="#" class="text-[#00AD8E] hover:underline">Terms of Service</a> and <a href="#" class="text-[#00AD8E] hover:underline">Privacy Policy</a>.
                </p>
            </form>
        </div>
    </div>

    <!-- JavaScript for pricing toggle and form handling -->
    <script>
        // Variables for plan selection
        let selectedPlan = null;
        let selectedBillingCycle = 'monthly';

        // Define pricing information for each plan
        const pricingInfo = {
            educator: {
                monthly: {
                    price: '$18.99/month',
                    priceId: 'price_1OsYTgAIe95LGsSc6Nh3Ck0r',
                    billingInfo: 'Billed monthly'
                },
                yearly: {
                    price: '$16.99/month',
                    priceId: 'price_1OsYTgAIe95LGsSc6Nh3Ck0r', // Update with actual yearly price ID
                    billingInfo: 'Billed annually ($203.88/year)'
                }
            },
            institution: {
                monthly: {
                    price: '$19.99/seat/month',
                    priceId: 'price_institution_monthly',
                    billingInfo: 'Min 3 seats, billed monthly'
                },
                yearly: {
                    price: '$17.99/seat/month',
                    priceId: 'price_institution_yearly',
                    billingInfo: 'Min 3 seats, billed annually ($215.88/seat/year)'
                }
            }
        };

        // Toggle between monthly and yearly billing
        document.getElementById('billing-toggle').addEventListener('change', function() {
            selectedBillingCycle = this.checked ? 'yearly' : 'monthly';
            
            // Toggle visibility of pricing elements
            const monthlyElements = document.querySelectorAll('.pricing-monthly');
            const yearlyElements = document.querySelectorAll('.pricing-yearly');
            
            if (selectedBillingCycle === 'yearly') {
                monthlyElements.forEach(el => el.classList.add('hidden'));
                yearlyElements.forEach(el => el.classList.remove('hidden'));
            } else {
                monthlyElements.forEach(el => el.classList.remove('hidden'));
                yearlyElements.forEach(el => el.classList.add('hidden'));
            }
        });

        // Function to start the free trial process
        function startFreeTrial(planId) {
            // Set the selected plan
            selectedPlan = planId;
            
            // Show the signup form
            document.getElementById('signup-form-container').classList.remove('hidden');
            
            // Scroll to the signup form
            document.getElementById('signup-form-container').scrollIntoView({ behavior: 'smooth' });
            
            // Focus on the first input field
            document.getElementById('name').focus();
            
            // Add a message to inform the user
            const formMessage = document.getElementById('form-message');
            formMessage.innerHTML = '<p>Complete your information to start your 3-day free trial</p>';
            formMessage.classList.remove('hidden');
            
            // Set a flag to indicate this is a direct trial signup
            window.directTrialSignup = true;
        }

        // Handle form submission
        document.getElementById('signup-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            // Simple validation
            if (!name || !email || !password || !confirmPassword) {
                alert('Please fill in all fields');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }
            
            if (!selectedPlan) {
                alert('Please select a plan');
                return;
            }
            
            // Disable submit button to prevent multiple submissions
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            
            // Get the pricing ID based on selected plan and billing cycle
            let priceId = pricingInfo[selectedPlan][selectedBillingCycle].priceId;
            
            // Create checkout session with 3-day trial
            fetch('api/create-checkout-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    email: email, 
                    priceId: priceId
                })
            })
            .then(response => response.json())
            .then(session => {
                if (session.id) {
                    // Store user data in localStorage for later use after checkout
                    localStorage.setItem('gg_signup_data', JSON.stringify({
                        name: name,
                        email: email,
                        password: password,
                        plan: selectedPlan,
                        billing_cycle: selectedBillingCycle
                    }));
                    
                    // Redirect to Stripe Checkout
                    window.location.href = 'https://checkout.stripe.com/pay/' + session.id;
                } else {
                    alert(session.error || 'Failed to create checkout session. Please try again.');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Start Your 3-Day Free Trial';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
                submitButton.disabled = false;
                submitButton.textContent = 'Start Your 3-Day Free Trial';
            });
        });
    </script>
</body>
</html>
