<?php
/**
 * Subscription Check Middleware
 * 
 * This file checks if a user has an active subscription or is in trial period.
 * If not, they are redirected to the pricing page with an upgrade message.
 * 
 * Include this file at the beginning of any page that requires subscription.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'c.php';
require_once 'error-log.php';

// Function to check subscription status
function checkSubscription() {
    global $conn;
    
    // If user is not logged in, redirect to login page
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Check if user has an active subscription
        $stmt = $conn->prepare("SELECT uid, email, name, subscription_status, trial_end_date, active_sub, plan_id
            FROM users
            WHERE uid = ?");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // User not found
            log_error('subscription_check_error', 'User not found', ['user_id' => $userId]);
            header('Location: /pricing.php?error=user_not_found');
            exit;
        }
        
        $user = $result->fetch_assoc();
        
        // Get trial end date
        $trialEndDate = $user['trial_end_date'];
        
        // Check subscription status
        $status = $user['subscription_status'] ?? null;
        $activeSub = $user['active_sub'] ?? 0;
        
        // User has active subscription
        if ($status === 'active' || $activeSub == 1) {
            return [
                'status' => $status,
                'trial_ends_at' => $trialEndDate,
                'is_trial' => ($status === 'trialing'),
                'trial_expired' => false
            ];
        }
        
        // User is in trial period
        if ($status === 'trialing' && $trialEndDate) {
            $trialEnd = new DateTime($trialEndDate);
            $now = new DateTime();
            
            if ($trialEnd > $now) {
                // Trial still active
                return [
                    'status' => $status,
                    'trial_ends_at' => $trialEndDate,
                    'is_trial' => true,
                    'trial_expired' => false
                ];
            } else {
                // Trial expired
                log_debug('Trial expired', [
                    'user_id' => $userId,
                    'status' => $status,
                    'trial_end_date' => $trialEndDate,
                    'requested_url' => $_SERVER['REQUEST_URI']
                ]);
                
                // Redirect to pricing page with appropriate message
                header('Location: /pricing.php?message=trial_expired');
                exit;
            }
        }
        
        // No active subscription or trial
        log_debug('No active subscription', [
            'user_id' => $userId,
            'status' => $status,
            'requested_url' => $_SERVER['REQUEST_URI']
        ]);
        
        // Redirect to pricing page
        header('Location: /pricing.php?message=subscription_required');
        exit;
        
    } catch (Exception $e) {
        // Log error
        log_error('subscription_check_error', $e->getMessage(), [
            'user_id' => $userId,
            'trace' => $e->getTraceAsString()
        ]);
        
        // Redirect to pricing page with error message
        header('Location: /pricing.php?error=system_error');
        exit;
    }
}

// Check subscription and get subscription info
$subscriptionInfo = checkSubscription();

// Set global variables for use in the page
$isSubscriptionActive = in_array($subscriptionInfo['status'], ['active', 'trialing']);
$isTrialActive = $subscriptionInfo['is_trial'] && !$subscriptionInfo['trial_expired'];
$trialEndsDate = $isTrialActive ? date('F j, Y', strtotime($subscriptionInfo['trial_ends_at'])) : null;
$daysLeftInTrial = $isTrialActive ? ceil((strtotime($subscriptionInfo['trial_ends_at']) - time()) / (60 * 60 * 24)) : 0;
?>
