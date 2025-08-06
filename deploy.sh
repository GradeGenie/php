#!/bin/bash

# Deployment script for GradeGenie Subscription System
# This script deploys the subscription system files to the production server

# Server details
SERVER="104.131.179.92"
REMOTE_PATH="/var/www/html/new"
SSH_USER="root"

echo "Deploying GradeGenie Subscription System to $SERVER:$REMOTE_PATH"

# Create necessary directories on the server
ssh $SSH_USER@$SERVER "mkdir -p $REMOTE_PATH/api"

# Upload API files
echo "Uploading API files..."
scp /Users/serenec/GG\ NEW/api/seed_stripe_plans.php $SSH_USER@$SERVER:$REMOTE_PATH/api/
scp /Users/serenec/GG\ NEW/api/create_checkout_session.php $SSH_USER@$SERVER:$REMOTE_PATH/api/
scp /Users/serenec/GG\ NEW/api/check-subscription.php $SSH_USER@$SERVER:$REMOTE_PATH/api/
scp /Users/serenec/GG\ NEW/api/create-user-from-checkout.php $SSH_USER@$SERVER:$REMOTE_PATH/api/
scp /Users/serenec/GG\ NEW/api/error-handler.php $SSH_USER@$SERVER:$REMOTE_PATH/api/

# Rename stripe-webhook.php to match your existing endpoint
cp /Users/serenec/GG\ NEW/api/stripe-webhook.php /Users/serenec/GG\ NEW/api/webhook.php
scp /Users/serenec/GG\ NEW/api/webhook.php $SSH_USER@$SERVER:$REMOTE_PATH/api/

# Only upload c.php if it doesn't exist on the server to avoid overwriting existing credentials
ssh $SSH_USER@$SERVER "if [ ! -f $REMOTE_PATH/api/c.php ]; then echo 'Uploading c.php...'; exit 1; else echo 'c.php already exists, skipping...'; exit 0; fi"
if [ $? -eq 1 ]; then
  scp /Users/serenec/GG\ NEW/api/c.php $SSH_USER@$SERVER:$REMOTE_PATH/api/
fi

# Upload frontend files
echo "Uploading frontend files..."
scp /Users/serenec/GG\ NEW/pricing.php $SSH_USER@$SERVER:$REMOTE_PATH/
scp /Users/serenec/GG\ NEW/success.php $SSH_USER@$SERVER:$REMOTE_PATH/
scp /Users/serenec/GG\ NEW/cancel.php $SSH_USER@$SERVER:$REMOTE_PATH/
scp /Users/serenec/GG\ NEW/debug.php $SSH_USER@$SERVER:$REMOTE_PATH/

# Create error-log.php if it doesn't exist
cat > /tmp/error-log.php << 'EOL'
<?php
/**
 * Error Logging Functions
 * 
 * This file contains functions for logging errors and debug messages.
 */

// Define log directory
define('LOG_DIR', dirname(__DIR__) . '/logs');

// Create log directory if it doesn't exist
if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

/**
 * Log an error message
 * 
 * @param string $type Error type
 * @param string $message Error message
 * @param array $context Additional context data
 */
function log_error($type, $message, $context = []) {
    $logFile = LOG_DIR . '/error.log';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'context' => $context
    ];
    
    $logLine = json_encode($logData) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

/**
 * Log a debug message
 * 
 * @param string $message Debug message
 * @param array $context Additional context data
 */
function log_debug($message, $context = []) {
    $logFile = LOG_DIR . '/debug.log';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context
    ];
    
    $logLine = json_encode($logData) . "\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
}
?>
EOL

scp /tmp/error-log.php $SSH_USER@$SERVER:$REMOTE_PATH/api/

# Set permissions
echo "Setting permissions..."
ssh $SSH_USER@$SERVER "chmod 755 $REMOTE_PATH/api/*.php"
ssh $SSH_USER@$SERVER "chmod 755 $REMOTE_PATH/*.php"

# Run the seed_stripe_plans.php script on the server to create Stripe products and prices
echo "Creating Stripe products and prices..."
ssh $SSH_USER@$SERVER "php $REMOTE_PATH/api/seed_stripe_plans.php"

# Create a log directory if it doesn't exist
ssh $SSH_USER@$SERVER "mkdir -p $REMOTE_PATH/logs && chmod 777 $REMOTE_PATH/logs"

echo "Deployment complete!"
echo "Access your subscription system at: https://app.getgradegenie.com/new/pricing.php"
echo ""
echo "IMPORTANT NEXT STEPS:"
echo "1. Update your existing Stripe Webhook at https://dashboard.stripe.com/webhooks"
echo "   - Make sure your webhook at https://app.getgradegenie.com/api/webhook.php"
echo "     is listening for these events: checkout.session.completed, customer.subscription.created,"
echo "     customer.subscription.updated, customer.subscription.deleted, invoice.payment_succeeded,"
echo "     invoice.payment_failed"
echo ""
echo "2. Test the subscription system by signing up for a plan"
echo "   - Use test card: 4242 4242 4242 4242"
echo "   - Any future date for expiration"
echo "   - Any 3-digit CVC"
echo ""
echo "3. Monitor logs at $REMOTE_PATH/logs/"
echo "   - Check for any errors or issues"
echo ""
echo "4. Update your Stripe API keys in c.php if needed"
echo "   - Make sure you're using your live keys in production"
