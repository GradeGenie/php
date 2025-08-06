<?php
// Enable error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require 'vendor/autoload.php';
require_once __DIR__ . '/../load_env.php';

use \Stripe\Stripe;
use \Stripe\Exception\ApiErrorException;

// Set your Stripe secret key
Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

// Database connection credentials
$host = 'localhost';
$username = 'root';
$password = 'JustWing1t';
$database = 'grady';

// Create database connection
$dsn = "mysql:host=$host;dbname=$database";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Sanitize and validate inputs
$first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
$last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email address");
}
$password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
$plan = filter_var(trim($_POST['plan']), FILTER_SANITIZE_STRING);
$stripeToken = filter_var(trim($_POST['stripeToken']), FILTER_SANITIZE_STRING);

if (!$first_name || !$last_name || !$email || !$password || !$plan || !$stripeToken) {
    die("Invalid input");
}

// Save user information in the database
try {
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, plan) VALUES (:first_name, :last_name, :email, :password, :plan)");
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':password' => $password,
        ':plan' => $plan
    ]);
} catch (PDOException $e) {
    die("Database insert failed: " . $e->getMessage());
}

// Handle the plan selection and pricing
$plan_prices = [
    'starter' => ['price' => 2900, 'name' => "Starter Plan"],
    'basic' => ['price' => 5900, 'name' => "Basic Plan"],
    'pro' => ['price' => 8900, 'name' => "Pro Plan"],
    'premium' => ['price' => 9900, 'name' => "Premium Plan"],
    'enterprise' => ['price' => 0, 'name' => "Enterprise Plan"]
];

if (!array_key_exists($plan, $plan_prices)) {
    die("Invalid plan selected");
}

$selected_plan = $plan_prices[$plan];

// Redirect enterprise plan to a specific URL
if ($plan === 'enterprise') {
    header("Location: https://cal.com/fastnfurious/30min");
    exit();
}

// Create a new Stripe customer and charge the customer
try {
    $customer = \Stripe\Customer::create([
        'email' => $email,
        'source' => $stripeToken,
        'name' => $first_name . ' ' . $last_name,
    ]);

    if ($selected_plan['price'] > 0) {
        $charge = \Stripe\Charge::create([
            'amount' => $selected_plan['price'],
            'currency' => 'usd',
            'customer' => $customer->id,
            'description' => $selected_plan['name'],
        ]);
    }

    // Redirect to a success page
    header("Location: https://buy.stripe.com/7sI01Y3S76F9dTqeUV");
    exit();
} catch (ApiErrorException $e) {
    // Handle Stripe API errors
    die("Payment processing failed: " . $e->getMessage());
}
?>
