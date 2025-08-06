<?php
// Include Stripe library
require_once '../vendor/autoload.php';

// Stripe API Keys
require_once __DIR__ . '/../load_env.php';
// Set Stripe API key
\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

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
    
    // Save the price IDs to a file for easy reference
    $priceIdsFile = __DIR__ . '/price-ids.php';
    $priceIdsContent = "<?php\n";
    $priceIdsContent .= "// Generated on " . date('Y-m-d H:i:s') . "\n";
    $priceIdsContent .= "define('MONTHLY_PRICE_ID', '" . $monthlyPrice->id . "');\n";
    $priceIdsContent .= "define('YEARLY_PRICE_ID', '" . $yearlyPrice->id . "');\n";
    $priceIdsContent .= "?>";
    
    file_put_contents($priceIdsFile, $priceIdsContent);
    echo "Price IDs saved to " . $priceIdsFile . "\n";
    
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
