<?php
// Get the new price IDs
require_once 'price-ids.php';

// Define the file path
$signupFilePath = '../signup.php';

// Read the file content
$fileContent = file_get_contents($signupFilePath);

// Check if the file was read successfully
if ($fileContent === false) {
    die("Error reading file: $signupFilePath");
}

// Replace the old price IDs with the new ones
$patterns = [
    '/priceId = \'price_[a-zA-Z0-9]+\'; \/\/ Monthly Educator plan/',
    '/priceId = \'price_[a-zA-Z0-9]+\'; \/\/ Yearly Educator plan/'
];

$replacements = [
    "priceId = '" . MONTHLY_PRICE_ID . "'; // Monthly Educator plan",
    "priceId = '" . YEARLY_PRICE_ID . "'; // Yearly Educator plan"
];

$updatedContent = preg_replace($patterns, $replacements, $fileContent);

// Check if any replacements were made
if ($updatedContent === $fileContent) {
    echo "No price ID replacements were made. The pattern might not match.\n";
    
    // Try a more general search and replace
    $monthlyPattern = '/price_[a-zA-Z0-9]+/'; 
    $yearlyPattern = '/price_[a-zA-Z0-9]+/'; 
    
    // Find all occurrences
    preg_match_all($monthlyPattern, $fileContent, $matches);
    
    echo "Found the following price IDs in the file:\n";
    foreach ($matches[0] as $match) {
        echo $match . "\n";
    }
    
    echo "\nPlease manually update the price IDs in the signup.php file with:\n";
    echo "Monthly price ID: " . MONTHLY_PRICE_ID . "\n";
    echo "Yearly price ID: " . YEARLY_PRICE_ID . "\n";
    
} else {
    // Write the updated content back to the file
    if (file_put_contents($signupFilePath, $updatedContent) !== false) {
        echo "Successfully updated price IDs in $signupFilePath\n";
    } else {
        echo "Error writing to file: $signupFilePath\n";
    }
}

echo "\nTo test the signup flow, use these values:\n";
echo "- Test card number: 4242 4242 4242 4242\n";
echo "- Expiration date: Any future date (e.g., 12/28)\n";
echo "- CVC: Any 3 digits (e.g., 123)\n";
echo "- ZIP: Any 5 digits (e.g., 10001)\n";
?>
