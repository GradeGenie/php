<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
        }
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
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
        .testimonial-container {
            margin-top: 50px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .testimonial {
            border-radius: 8px;
            background-color: #ecf0f1;
            padding: 20px;
            max-width: 30%;
            text-align: left;
            font-style: italic;
        }
        .testimonial:before {
            content: "“";
            font-size: 36px;
            color: #3498db;
            vertical-align: middle;
            margin-right: 10px;
        }
        .testimonial:after {
            content: "”";
            font-size: 36px;
            color: #3498db;
            vertical-align: middle;
            margin-left: 10px;
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
        form#signup-form {
    padding: 0px 300px;
    text-align: left;
}
        @media (max-width: 600px) {
            .plans {
                flex-direction: column;
                align-items: center;
            }
            .plan-box {
                max-width: 100%;
            }
            form#signup-form {
                padding: 0px 20px;
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
            margin-top: 50px;
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
.video-container {
            margin-top: 50px;
            text-align: center;
        }
        .video-container iframe {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .refer-friend-btn {
            display: inline-block;
            background-color: #2ecc71;
            color: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .refer-friend-btn:hover {
            background-color: #27ae60;
        }

        .refer-friend-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container" id="signupContainer">
        <h1 id="mainHeading">Join 30,000+ Educators Saving Millions of Hours with GradeGenie</h1>
        <div class="signup-card">
            <form id="signup-form">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="Jane" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Smith" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="jane@school.edu" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="button" id="continue-button">Continue</button>
                <p class="signup-text">Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>

    <div class="container" id="plan-selection" style="display:none;">
        <h2 id="paymentHeader">Select a Payment Plan</h2>
        <div class="toggle-container">
            <span class="toggle-label">Monthly</span>
            <label class="toggle-switch">
                <input type="checkbox" id="yearly-toggle" checked>
                <span class="slider"></span>
            </label>
            <span class="toggle-label">Yearly</span>
        </div>
        <div class="plans">
            <!-- Educator Plan -->
            <div class="plan-box" id="educator_plan" onclick="selectPlan('educator_plan')">
                <h3>Solo</h3>
                <p class="plan-price" id="educator_price">$14.99/month </p><span class="billing-info" id="educator_billing"></span>
                <ul class="plan-benefits">
                    <li>Grade up to 400 assignments 10x faster, for 1</li>
                    <li>Create detailed and targeted rubrics in seconds</li>
                    <li>Rubric Library to manage and update both uploaded and custom rubrics</li>
                    <li>Research-backed feedback designed to enhance performance</li>
                    <li>Progress Overview & Class Performance Insights</li>
                    <li>Manage your classes, assignments, and submissions efficiently</li>
                    <li>Priority Support</li>
                    <div class="greenBtn">Choose Plan</div>
                </ul>
            </div>
            <!-- School Plan -->
            <div class="plan-box" id="school_plan" onclick="selectPlan('school_plan')">
                <h3>Team</h3>
                <p class="plan-price" id="school_price">$749.50/month </p><span class="billing-info" id="school_billing"></span>
                <ul class="plan-benefits">
                    <li>Unlimited grading assistance for up to 50</li>
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
                <h3>Pro</h3>
                <p class="plan-price" id="uni_price">$1,499.00/month </p><span class="billing-info" id="uni_billing"></span>
                <ul class="plan-benefits">
                    <li>Unlimited grading assistance for up to 100</li>
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

    <div class="testimonial-container">
        <div class="testimonial">
            "GradeGenie saved me weeks of work. My students appreciate the fast turnaround, and I love how quick, easy, and accurate it is."
            <br><strong>- Hannah, School Teacher</strong>
        </div>
        <div class="testimonial">
            "Finding and managing TAs used to be a nightmare. With GradeGenie, I have a reliable, consistent grading assistant, freeing up my time for research."
            <br><strong>- Dane, University Professor</strong>
        </div>
        <div class="testimonial">
            "The feedback it provides is much more detailed than I have time to give, and I can still add my comments and adjust grades. I can’t imagine going back to the old way!"
            <br><strong>- Doras, Tutor</strong>
        </div>
    </div>

    <div class="video-container">
        <h2>See GradeGenie in Action</h2>
        <iframe width="560" height="315" src="https://www.youtube.com/embed/7O3vFOZDVxc" title="GradeGenie Explainer Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.getElementById('continue-button').addEventListener('click', function() {
            document.getElementById('signupContainer').style.display = 'none';
            document.getElementById('plan-selection').style.display = 'block';
        });

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

            document.getElementById('educator_price').textContent = isYearly ? '$12.49/month' : '$14.99/month';
            document.getElementById('educator_billing').textContent = isYearly ? '$149.88 billed yearly (Save 25%)' : '';

            document.getElementById('school_price').textContent = isYearly ? '$624.50/month' : '$749.50/month';
            document.getElementById('school_billing').textContent = isYearly ? '$7,494 billed yearly (Save $3,000)' : '';

            document.getElementById('uni_price').textContent = isYearly ? '$1,249.00/month' : '$1,499.00/month';
            document.getElementById('uni_billing').textContent = isYearly ? '$14,988 billed yearly (Save $3,000)' : '';
        });

        // Trigger change event on page load to show yearly pricing
window.onload = function() {
    document.getElementById('yearly-toggle').dispatchEvent(new Event('change'));
};

        document.getElementById('checkout-button').addEventListener('click', function() {
            const email = document.getElementById('email').value;
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const password = document.getElementById('password').value;
            const yearlyBilling = document.getElementById('yearly-toggle').checked;
            const stripe = Stripe('<?php echo $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? ''; ?>');
            let priceId = '';

            if (selectedPlan === 'educator_plan' && !yearlyBilling) {
                priceId = 'price_1PmX7dAIe95LGsScfRPzMv6E';
            } else if (selectedPlan === 'educator_plan' && yearlyBilling) {
                priceId = 'price_1PmX8UAIe95LGsSclb7Nj40E';
            } else if (selectedPlan === 'school_plan' && !yearlyBilling) {
                priceId = 'price_1PnrGWAIe95LGsScZaGXeRwd';
            } else if (selectedPlan === 'school_plan' && yearlyBilling) {
                priceId = 'price_1PnozdAIe95LGsSccmEKIi9R';
            } else if (selectedPlan === 'uni_plan' && !yearlyBilling) {
                priceId = 'price_1PmX9eAIe95LGsScgcAeNj9r';
            } else if (selectedPlan === 'uni_plan' && yearlyBilling) {
                priceId = 'price_1PmXAtAIe95LGsScShVpe3X7';
            }

            if (priceId) {
                var checkout_session_id = "";
                fetch('api/create-checkout-session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email, priceId: priceId })
                })
                .then(response => response.json())
                .then(session => {
                    if (session.id) {
                        checkout_session_id = session.id;
                        return fetch('api/signup_create_user.php', {
                            method: 'POST', 
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                first_name: firstName,
                                last_name: lastName,
                                email: email,
                                password: password,
                                stripeID: session.id
                            })
                        });
                    } else {
                        console.error('Error:', session.error);
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        return stripe.redirectToCheckout({ sessionId: checkout_session_id });
                    } else {
                        alert('Error creating user: ' + result.message);
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
</body>
</html>
