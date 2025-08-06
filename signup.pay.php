<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 80%;
            max-width: 1200px;
            text-align: center;
        }
        h1 {
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
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .plan-box {
            background-color: #fff;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            flex: 1;
            min-width: 300px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-box:hover {
            background-color: #f9f9f9;
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-color: #16a085;
        }
        .plan-box h3 {
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
            <div class="plan-box" id="educator_plan" onclick="redirectToCheckout('price_1PmX7dAIe95LGsScfRPzMv6E', 'price_1PmX8UAIe95LGsSclb7Nj40E')">
                <h3>Educator Plan</h3>
                <p class="plan-price" id="educator_price">$14.99/month <span class="billing-info" id="educator_billing"></span></p>
                <ul class="plan-benefits">
                    <li>Grade multiple assignments 10x faster, accurately and efficiently</li>
                    <li>Create detailed and targeted rubrics in seconds</li>
                    <li>Rubric Library to manage and update both uploaded and custom rubrics</li>
                    <li>Research-backed feedback designed to enhance performance</li>
                    <li>Grading Progress Overview & Insights into Performance</li>
                    <li>Manage your classes, assignments, and submissions efficiently</li>
                    <li>Priority Support</li>
                </ul>
            </div>
            <!-- School Plan -->
            <div class="plan-box" id="school_plan" onclick="redirectToCheckout('price_1PnrGWAIe95LGsScZaGXeRwd', 'price_1PnozdAIe95LGsSccmEKIi9R')">
                <h3>School Plan</h3>
                <p class="plan-price" id="school_price">$749.50/month <span class="billing-info" id="school_billing"></span></p>
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
                </ul>
            </div>
            <!-- Uni Plan -->
            <div class="plan-box" id="uni_plan" onclick="redirectToCheckout('price_1PmX9eAIe95LGsScgcAeNj9r', 'price_1PmXAtAIe95LGsScShVpe3X7')">
                <h3>Uni Plan</h3>
                <p class="plan-price" id="uni_price">$1,499.00/month <span class="billing-info" id="uni_billing"></span></p>
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
                </ul>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Handle the toggle switch between monthly and yearly pricing
        document.getElementById('yearly-toggle').addEventListener('change', function() {
            const isYearly = this.checked;

            document.getElementById('educator_price').textContent = isYearly ? '$12.49/month' : '$14.99/month';
            document.getElementById('educator_billing').textContent = isYearly ? '($149.88 billed yearly)' : '';

            document.getElementById('school_price').textContent = isYearly ? '$624.50/month' : '$749.50/month';
            document.getElementById('school_billing').textContent = isYearly ? '($7,494.00 billed yearly)' : '';

            document.getElementById('uni_price').textContent = isYearly ? '$1,249.00/month' : '$1,499.00/month';
            document.getElementById('uni_billing').textContent = isYearly ? '($14,988.00 billed yearly)' : '';
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
