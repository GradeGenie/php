<?php
/**
 * Stripe Plans Seeder
 * 
 * This script creates or updates Stripe products and prices for GradeGenie subscription plans.
 * 
 * Requirements:
 * - Run `composer require stripe/stripe-php:^12` in the project directory
 * - Stripe API keys are defined in c.php
 * 
 * Usage:
 * php seed_stripe_plans.php
 */

// Include dependencies
require_once '../vendor/autoload.php';
require_once 'c.php';
require_once 'error-log.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize Stripe with the secret key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Define plans
$planDefinitions = [
    'educator' => [
        'name' => 'Educator Plan',
        'description' => 'Perfect for individual teachers and educators',
        'monthly' => [
            'amount' => 1899, // $18.99 in cents
            'interval' => 'month'
        ],
        'annual' => [
            'amount' => 16990, // $169.90 in cents (10% discount)
            'interval' => 'year'
        ]
    ],
    'institution' => [
        'name' => 'Institution Plan',
        'description' => 'Ideal for schools and educational institutions',
        'monthly' => [
            'amount' => 1999, // $19.99 in cents
            'interval' => 'month',
            'min_quantity' => 3
        ],
        'annual' => [
            'amount' => 17990, // $179.90 in cents (10% discount)
            'interval' => 'year',
            'min_quantity' => 3
        ]
    ],
    'enterprise' => [
        'name' => 'Enterprise Plan',
        'description' => 'Custom solutions for large organizations',
        'contact_sales' => true
    ]
];

// Array to store created/updated plan data
$plans = [];

try {
    // Check if plans table exists, create if not
    $checkTableQuery = "SHOW TABLES LIKE 'plans'";
    $result = $conn->query($checkTableQuery);
    
    if ($result->num_rows == 0) {
        // Create plans table
        $createTableQuery = "CREATE TABLE plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plan_key VARCHAR(50) NOT NULL UNIQUE,
            product_id VARCHAR(100) NOT NULL,
            price_id_month VARCHAR(100),
            price_id_year VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTableQuery)) {
            throw new Exception("Error creating plans table: " . $conn->error);
        }
        
        log_debug('Created plans table');
    }

    // Process each plan
    foreach ($planDefinitions as $planKey => $planData) {
        // Check if product already exists
        $existingProduct = null;
        $existingProducts = \Stripe\Product::all(['limit' => 100]);
        
        foreach ($existingProducts->data as $product) {
            if ($product->name === $planData['name']) {
                $existingProduct = $product;
                break;
            }
        }
        
        // Create or use existing product
        if ($existingProduct) {
            $product = $existingProduct;
            log_debug("Using existing product: {$product->id} ({$product->name})");
        } else {
            $product = \Stripe\Product::create([
                'name' => $planData['name'],
                'description' => $planData['description']
            ]);
            log_debug("Created new product: {$product->id} ({$product->name})");
        }
        
        $plans[$planKey] = [
            'product_id' => $product->id,
            'name' => $planData['name']
        ];
        
        // For Enterprise plan, create a placeholder price for "Contact Sales"
        if (isset($planData['contact_sales']) && $planData['contact_sales']) {
            $priceMonthly = \Stripe\Price::create([
                'product' => $product->id,
                'unit_amount' => 0,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => 'month'
                ],
                'nickname' => 'Contact Sales'
            ]);
            
            $plans[$planKey]['price_id_month'] = $priceMonthly->id;
            $plans[$planKey]['price_id_year'] = $priceMonthly->id; // Use same ID for both
        } else {
            // Create monthly price
            $priceMonthly = \Stripe\Price::create([
                'product' => $product->id,
                'unit_amount' => $planData['monthly']['amount'],
                'currency' => 'usd',
                'recurring' => [
                    'interval' => $planData['monthly']['interval']
                ]
            ]);
            
            // Create annual price
            $priceAnnual = \Stripe\Price::create([
                'product' => $product->id,
                'unit_amount' => $planData['annual']['amount'],
                'currency' => 'usd',
                'recurring' => [
                    'interval' => $planData['annual']['interval']
                ]
            ]);
            
            $plans[$planKey]['price_id_month'] = $priceMonthly->id;
            $plans[$planKey]['price_id_year'] = $priceAnnual->id;
        }
        
        // Upsert plan data into database
        $stmt = $conn->prepare("INSERT INTO plans (plan_key, product_id, price_id_month, price_id_year) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE 
                               product_id = VALUES(product_id), 
                               price_id_month = VALUES(price_id_month), 
                               price_id_year = VALUES(price_id_year)");
        
        $stmt->bind_param("ssss", 
            $planKey, 
            $plans[$planKey]['product_id'], 
            $plans[$planKey]['price_id_month'], 
            $plans[$planKey]['price_id_year']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error saving plan to database: " . $stmt->error);
        }
        
        log_debug("Saved plan to database: $planKey");
    }
    
    // Return success response with plans data
    echo json_encode([
        'success' => true,
        'plans' => $plans
    ]);
    
} catch (Exception $e) {
    // Log error
    log_error('stripe_plans_error', $e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
