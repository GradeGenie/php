<?php
// if user is not signed in, redirect them to signup.php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: signup.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Plan - GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
        }
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .toggle-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2ecc71;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .toggle-label {
            margin: 0 10px;
            font-weight: 600;
        }
        .plan-box {
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-box:hover {
            background-color: #f9f9f9;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .plan-box.active {
            border-color: #2ecc71;
            background-color: #eafaf1;
        }
        .plan-box h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .plan-price {
            font-size: 24px;
            font-weight: 600;
            color: #16a085;
            margin: 10px 0;
        }
        .plan-benefits {
            list-style-type: none;
            padding-left: 0;
        }
        .plan-benefits li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
        }
        .plan-benefits li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2ecc71;
            font-weight: bold;
        }
      
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
        }
        h1{
            margin-top:90px;
        }
        h3.subheading {
    text-align: center;
    color: #909090;
    margin-top: -25px;
    font-size: 20px;
}


h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }
        .toggle-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2ecc71;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .toggle-label {
            margin: 0 10px;
            font-weight: 600;
        }
        .plans {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .plan-box {
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 250px;
            max-width: 30%;
            text-align: left;
        }
        .plan-box:hover {
            background-color: #f9f9f9;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .plan-box.active {
            border-color: #2ecc71;
            background-color: #eafaf1;
        }
        .plan-box h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .plan-price {
            font-size: 24px;
            font-weight: 600;
            color: #16a085;
            margin: 10px 0;
        }
        .plan-price .billing-info {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .plan-benefits {
            list-style-type: none;
            padding-left: 0;
        }
        .plan-benefits li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
        }
        .plan-benefits li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2ecc71;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .plans {
                flex-direction: column;
                align-items: center;
            }
            .plan-box {
                max-width: 100%;
            }
        }
        .signup-text {
            font-size: 14px;
            color: #666666;
            margin-top: 10px;
            text-align: center;
        }
        .signup-text a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .signup-text a:hover {
            color: #45a049;
        }
        #paymentHeader{
            /* margin-top: 50px; */
        }
        form#signup-form {
    padding: 0px 300px;
    text-align: left;
}
#mainHeading{
    margin-top: 50px;
}
.plan-box {
    position: relative;
    padding-bottom: 75px;
}
.greenBtn {
    text-align: center;
    color: #fff;
    background: #16a085;
    padding: 12px;
    border-radius: 37px;
    font-weight: bold;
    position: absolute;
    bottom: 0;
    width: calc(100% - 40px);
    left: 0;
    margin: 20px;
}

    </style>
</head>
<body>
    <div class="container">
        <h1>You Don't Have a Plan Yet!</h1>
        <h3 class="subheading">To use GradeGenie, Choose a Plan Below</h3>
        <div class="container2" id="plan-selection">
            <h2 id="paymentHeader">Select a Payment Plan</h2>
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
            <div class="plan-box" id="educator_plan" onclick="selectPlan('educator_plan')">
                <h3>Educator Plan</h3>
                <p class="plan-price" id="educator_price">$14.99/month </p><span class="billing-info" id="educator_billing"></span>
                <ul class="plan-benefits">
                    <li>Grade unlimited assignments 10x faster, for 1 teacher</li>
                    <li>Create detailed and targeted rubrics in seconds</li>
                    <li>Rubric Library to manage and update both uploaded and custom rubrics</li>
                    <li>Research-backed feedback designed to enhance performance</li>
                    <li>Grading Progress Overview & Insights into Performance</li>
                    <li>Manage your classes, assignments, and submissions efficiently</li>
                    <li>Priority Support</li>
                    <div class="greenBtn">Choose Plan</div>
                </ul>
            </div>
            <!-- School Plan -->
            <div class="plan-box" id="school_plan" onclick="selectPlan('school_plan')">
                <h3>School Plan</h3>
                <p class="plan-price" id="school_price">$749.50/month </p><span class="billing-info" id="school_billing"></span>
                <ul class="plan-benefits">
                    <li>Unlimited grading assistance for up to 50 teachers</li>
                    <li>Bulk grading capabilities for large classes</li>
                    <li>AI usage Detection</li>
                    <li>Plagiarism Detection</li>
                    <li>Citation Checker</li>
                    <li>Custom LMS Integrations</li>
                    <li>Grammar and Spelling Checker</li>
                    <li>Custom rubric generator with advanced customization options</li>
                    <li>Tailored training and support</li>
                    <li>Dedicated Account Manager</li>
                    <div class="greenBtn">Choose Plan</div>
                </ul>
            </div>
            <!-- Uni Plan -->
            <div class="plan-box" id="uni_plan" onclick="selectPlan('uni_plan')">
                <h3>Uni Plan</h3>
                <p class="plan-price" id="uni_price">$1,499.00/month </p><span class="billing-info" id="uni_billing"></span>
                <ul class="plan-benefits">
                    <li>Unlimited grading assistance for up to 100 teachers</li>
                    <li>Bulk grading capabilities for large classes</li>
                    <li>AI usage Detection</li>
                    <li>Plagiarism Detection</li>
                    <li>Citation Checker</li>
                    <li>Custom LMS Integrations</li>
                    <li>Grammar and Spelling Checker</li>
                    <li>Custom rubric generator with advanced customization options</li>
                    <li>Tailored training and support</li>
                    <li>Dedicated Account Manager</li>
                    <div class="greenBtn">Choose Plan</div>
                </ul>
            </div>
        </div>
        <button type="button" id="checkout-button" style="display:none;">Proceed to Checkout</button>
        </div>

        <script src="https://js.stripe.com/v3/"></script>
        <script>


        let selectedPlan = null;

        function selectPlan(plan) {
            const allPlans = document.querySelectorAll('.plan-box');
            allPlans.forEach(box => box.classList.remove('active'));

            document.getElementById(plan).classList.add('active');
            selectedPlan = plan;

            // document.getElementById('checkout-button').style.display = 'block';
            $("#checkout-button").click();
        }

        document.getElementById('yearly-toggle').addEventListener('change', function() {
            const isYearly = this.checked;

            // Update Educator Plan pricing
            document.getElementById('educator_price').textContent = isYearly ? '$16.99 / seat / month' : '$18.99 / seat / month';
            document.getElementById('educator_billing').textContent = isYearly ? '$16.99 / seat / month, billed yearly' : '';

            // Update Institution Plan pricing
            document.getElementById('school_price').textContent = isYearly ? '$17.99 / seat / month (min 3 seats)' : '$19.99 / seat / month (min 3 seats)';
            document.getElementById('school_billing').textContent = isYearly ? '$17.99 / seat / month (min 3 seats), billed yearly' : '';

            // Enterprise plan
            document.getElementById('uni_price').textContent = 'Custom Pricing (min 3 seats)';
            document.getElementById('uni_billing').textContent = '';
        });


            function contactUs() {
                window.location.href = "mailto:hello@getgradegenie.com";
            }
            
            // Set yearly toggle to checked by default and trigger the change event when the page loads
            window.onload = function() {
                document.getElementById('yearly-toggle').checked = true;
                document.getElementById('yearly-toggle').dispatchEvent(new Event('change'));
            }
            
            document.getElementById('checkout-button').addEventListener('click', function() {
                // Use test key instead of live key
                const stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
                let priceId = '';
                const yearlyBilling = document.getElementById('yearly-toggle').checked;

                if (selectedPlan === 'educator_plan' && !yearlyBilling) {
                    priceId = 'price_1RSfDZPNghHxhsC6AXTelaP3'; // Monthly educator plan ($18.99/month)
                } else if (selectedPlan === 'educator_plan' && yearlyBilling) {
                    priceId = 'price_1RSfDZPNghHxhsC6WBDCIvQ8'; // Yearly educator plan ($16.99/month)
                } else if (selectedPlan === 'school_plan' && !yearlyBilling) {
                    // Use the institution plan price IDs (monthly)
                    priceId = 'price_1RSfDZPNghHxhsC6AXTelaP3'; // Using educator plan for now
                } else if (selectedPlan === 'school_plan' && yearlyBilling) {
                    // Use the institution plan price IDs (yearly)
                    priceId = 'price_1RSfDZPNghHxhsC6WBDCIvQ8'; // Using educator plan for now
                } else if (selectedPlan === 'uni_plan' && !yearlyBilling) {
                    // Enterprise plan - contact us
                    contactUs();
                    return;
                } else if (selectedPlan === 'uni_plan' && yearlyBilling) {
                    // Enterprise plan - contact us
                    contactUs();
                    return;
                }

                if (priceId) {
                    fetch('api/create-checkout-session-upgrade.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            priceId: priceId,
                            email: '<?php echo $_SESSION['user_email']; ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(session => {
                        if (session.id) {
                            stripe.redirectToCheckout({ sessionId: session.id });
                        } else {
                            console.error('Checkout session creation failed:', session.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    console.error('No plan selected or incorrect plan.');
                }
            });
        </script>
    </div>
</body>
</html>
