<?php
// Define the new price IDs
$monthlyPriceId = 'price_1RSfDZPNghHxhsC6AXTelaP3';
$yearlyPriceId = 'price_1RSfDZPNghHxhsC6WBDCIvQ8';

// Define the file path
$signupFilePath = '/Users/serenec/GG NEW/signup.php';

// Read the file content
$fileContent = file_get_contents($signupFilePath);

// Check if the file was read successfully
if ($fileContent === false) {
    die("Error reading file: $signupFilePath");
}

// Find the position where we need to update the price IDs
$startPos = strpos($fileContent, "// Monthly Educator plan");
if ($startPos === false) {
    echo "Could not find the price ID section in the file.\n";
    exit(1);
}

// Look for the price ID lines
$pattern = '/priceId = \'(price_[a-zA-Z0-9]+)\'/';
preg_match_all($pattern, $fileContent, $matches, PREG_OFFSET_CAPTURE);

if (count($matches[0]) < 2) {
    echo "Could not find both price IDs in the file.\n";
    echo "Please manually update the price IDs in signup.php:\n";
    echo "Monthly price ID: $monthlyPriceId\n";
    echo "Yearly price ID: $yearlyPriceId\n";
    exit(1);
}

// Replace the first occurrence (monthly)
$monthlyPos = $matches[0][0][1];
$monthlyOldId = $matches[1][0][0];
$fileContent = substr_replace(
    $fileContent, 
    "priceId = '$monthlyPriceId'", 
    $monthlyPos, 
    strlen("priceId = '$monthlyOldId'")
);

// Replace the second occurrence (yearly)
$yearlyPos = $matches[0][1][1];
$yearlyOldId = $matches[1][1][0];
$fileContent = substr_replace(
    $fileContent, 
    "priceId = '$yearlyPriceId'", 
    $yearlyPos, 
    strlen("priceId = '$yearlyOldId'")
);

// Write the updated content back to the file
if (file_put_contents($signupFilePath, $fileContent) !== false) {
    echo "Successfully updated price IDs in $signupFilePath\n";
    echo "Old monthly ID: $monthlyOldId -> New: $monthlyPriceId\n";
    echo "Old yearly ID: $yearlyOldId -> New: $yearlyPriceId\n";
} else {
    echo "Error writing to file: $signupFilePath\n";
}

echo "\nTo test the signup flow, use these values:\n";
echo "- Test card number: 4242 4242 4242 4242\n";
echo "- Expiration date: Any future date (e.g., 12/28)\n";
echo "- CVC: Any 3 digits (e.g., 123)\n";
echo "- ZIP: Any 5 digits (e.g., 10001)\n";
?>
