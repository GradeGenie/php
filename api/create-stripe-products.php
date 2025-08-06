<?php
// Include database connection and Stripe
require_once 'c.php';
require_once '../vendor/autoload.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Create webhook endpoint if it doesn't exist
try {
    // First check if we already have a webhook endpoint
    $endpoints = \Stripe\WebhookEndpoint::all(['limit' => 100]);
    $webhookExists = false;
    
    foreach ($endpoints->data as $endpoint) {
        if (strpos($endpoint->url, 'getgradegenie.com') !== false) {
            echo "Webhook already exists: " . $endpoint->id . "\n";
            $webhookExists = true;
            break;
        }
    }
    
    if (!$webhookExists) {
        // Create a new webhook endpoint
        $endpoint = \Stripe\WebhookEndpoint::create([
            'url' => 'https://app.getgradegenie.com/new/api/stripe-webhook.php',
            'enabled_events' => [
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
                'invoice.payment_succeeded',
                'invoice.payment_failed',
            ],
        ]);
        
        echo "Created webhook endpoint: " . $endpoint->id . "\n";
        echo "Webhook Secret: " . $endpoint->secret . "\n";
        echo "Please add this secret to your configuration.\n\n";
    }
} catch (\Exception $e) {
    echo "Error creating webhook: " . $e->getMessage() . "\n";
}

// Create Educator Plan product
try {
    // Check if product already exists
    $products = \Stripe\Product::all(['limit' => 100]);
    $educatorProductId = null;
    
    foreach ($products->data as $product) {
        if ($product->name === 'Educator Plan') {
            $educatorProductId = $product->id;
            echo "Educator Plan product already exists: " . $educatorProductId . "\n";
            break;
        }
    }
    
    if (!$educatorProductId) {
        // Create a new product
        $product = \Stripe\Product::create([
            'name' => 'Educator Plan',
            'description' => 'For individual teachers who want to save time and give better feedback',
        ]);
        
        $educatorProductId = $product->id;
        echo "Created Educator Plan product: " . $educatorProductId . "\n";
    }
    
    // Create monthly price
    $monthlyPrice = \Stripe\Price::create([
        'product' => $educatorProductId,
        'unit_amount' => 1899, // $18.99
        'currency' => 'usd',
        'recurring' => [
            'interval' => 'month',
        ],
        'nickname' => 'Educator Plan Monthly',
    ]);
    
    // Create yearly price
    $yearlyPrice = \Stripe\Price::create([
        'product' => $educatorProductId,
        'unit_amount' => 16990, // $169.90 (equivalent to $16.99/month for 10 months)
        'currency' => 'usd',
        'recurring' => [
            'interval' => 'year',
        ],
        'nickname' => 'Educator Plan Yearly',
    ]);
    
    echo "Created Monthly Price ID: " . $monthlyPrice->id . "\n";
    echo "Created Yearly Price ID: " . $yearlyPrice->id . "\n";
    
    echo "\nUpdate your JavaScript in signup.php with these price IDs:\n";
    echo "Monthly price_id: '" . $monthlyPrice->id . "'\n";
    echo "Yearly price_id: '" . $yearlyPrice->id . "'\n";
    
} catch (\Exception $e) {
    echo "Error creating products/prices: " . $e->getMessage() . "\n";
}

// Save webhook secret to configuration
try {
    // Define the webhook secret
    $webhookSecret = 'whsec_59SKxNluITIp1rD3aT06wbFaD1pc0G21';
    
    // Create or update the webhook secret file
    $webhookConfigFile = __DIR__ . '/webhook-config.php';
    $webhookConfig = "<?php\ndefine('STRIPE_WEBHOOK_SECRET', '" . $webhookSecret . "');\n?>";
    
    file_put_contents($webhookConfigFile, $webhookConfig);
    echo "\nWebhook secret saved to " . $webhookConfigFile . "\n";
    
} catch (\Exception $e) {
    echo "Error saving webhook secret: " . $e->getMessage() . "\n";
}
?>
