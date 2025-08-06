<?php
// Include the Stripe configuration file
require 'stripe-config.php';

// Example of using getPlanDetails to retrieve plan information
$educatorMonthly = getPlanDetails('educator', 'monthly');
$educatorYearly = getPlanDetails('educator', 'yearly');
$schoolMonthly = getPlanDetails('school', 'monthly');
$schoolYearly = getPlanDetails('school', 'yearly');
$uniMonthly = getPlanDetails('uni', 'monthly');
$uniYearly = getPlanDetails('uni', 'yearly');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <style>
        /* (CSS styling code as before) */
    </style>
</head>
<body>
    <div class="container">
        <h1>Join GradeGenie</h1>
        <div class="toggle-container">
            <span class="toggle-label">Monthly</span>
            <label class="toggle-switch">
                <input type="checkbox" id="yearly-toggle">
                <span class="slider"></span>
            </label>
            <span class="toggle-label">Yearly</span>
        </div>
        <div class="plans">
            <!-- Educator Plan -->
            <div class="plan-box" id="educator_plan" onclick="redirectToCheckout('<?php echo $educatorMonthly['price_id']; ?>', '<?php echo $educatorYearly['price_id']; ?>')">
                <h3>Educator Plan</h3>
                <p class="plan-price" id="educator_price"><?php echo $educatorMonthly['amount']; ?> <span class="billing-info" id="educator_billing"></span></p>
                <ul class="plan-benefits">
                    <li><?php echo $educatorMonthly['description']; ?></li>
                </ul>
            </div>
            <!-- School Plan -->
            <div class="plan-box" id="school_plan" onclick="redirectToCheckout('<?php echo $schoolMonthly['price_id']; ?>', '<?php echo $schoolYearly['price_id']; ?>')">
                <h3>School Plan</h3>
                <p class="plan-price" id="school_price"><?php echo $schoolMonthly['amount']; ?> <span class="billing-info" id="school_billing"></span></p>
                <ul class="plan-benefits">
                    <li><?php echo $schoolMonthly['description']; ?></li>
                </ul>
            </div>
            <!-- Uni Plan -->
            <div class="plan-box" id="uni_plan" onclick="redirectToCheckout('<?php echo $uniMonthly['price_id']; ?>', '<?php echo $uniYearly['price_id']; ?>')">
                <h3>Uni Plan</h3>
                <p class="plan-price" id="uni_price"><?php echo $uniMonthly['amount']; ?> <span class="billing-info" id="uni_billing"></span></p>
                <ul class="plan-benefits">
                    <li><?php echo $uniMonthly['description']; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Handle the toggle switch between monthly and yearly pricing
        document.getElementById('yearly-toggle').addEventListener('change', function() {
            const isYearly = this.checked;

            document.getElementById('educator_price').textContent = isYearly ? '<?php echo $educatorYearly['amount']; ?>' : '<?php echo $educatorMonthly['amount']; ?>';
            document.getElementById('educator_billing').textContent = isYearly ? '<?php echo $educatorYearly['amount']; ?>' : '';

            document.getElementById('school_price').textContent = isYearly ? '<?php echo $schoolYearly['amount']; ?>' : '<?php echo $schoolMonthly['amount']; ?>';
            document.getElementById('school_billing').textContent = isYearly ? '<?php echo $schoolYearly['amount']; ?>' : '';

            document.getElementById('uni_price').textContent = isYearly ? '<?php echo $uniYearly['amount']; ?>' : '<?php echo $uniMonthly['amount']; ?>';
            document.getElementById('uni_billing').textContent = isYearly ? '<?php echo $uniYearly['amount']; ?>' : '';
        });

        // Redirect to Stripe Checkout with the appropriate pricing ID
        function redirectToCheckout(monthlyPriceId, yearlyPriceId) {
            const isYearly = document.getElementById('yearly-toggle').checked;
            const priceId = isYearly ? yearlyPriceId : monthlyPriceId;

            fetch('api/create-checkout-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: 'user@example.com',  // Replace with the actual user's email
                    priceId: priceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    const stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
                    stripe.redirectToCheckout({
                        sessionId: data.id
                    });
                } else {
                    alert('Error creating Stripe session: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
