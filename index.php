<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for Stripe session ID in URL
if (isset($_GET['session_id'])) {
    // Store the session ID
    $_SESSION['stripe_session_id'] = $_GET['session_id'];
    
    // Ensure user is marked as logged in
    $_SESSION['logged_in'] = true;
    
    // Set default user info if missing
    if (!isset($_SESSION['user_first_name']) || empty($_SESSION['user_first_name'])) {
        $_SESSION['user_first_name'] = 'User';
    }
    
    // Log for debugging
    error_log('Stripe session ID detected in index.php: ' . $_GET['session_id']);
    error_log('Session variables: ' . print_r($_SESSION, true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="https://app.getgradegenie.com/assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://app.getgradegenie.com/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://app.getgradegenie.com/assets/favicon-16x16.png">
    <style>
        :root {
            --primary-color: #19A37E;
            --primary-hover: #45a049;
            --secondary-color: #2196f3;
            --secondary-hover: #1e88e5;
            --tertiary-color: #ff9800;
            --tertiary-hover: #f57c00;
            --bg-color: #f4f4f4;
            --white: #ffffff;
            --text-primary: #333333;
            --text-secondary: #666666;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            line-height: 1.6;
        }
        h1,h2,h3,h4,h5,h6{
            font-family: 'Onest', sans-serif;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--white);
            padding: 2rem;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }

        .nav-item:hover, .nav-item.active {
            background-color: #e8f5e9;
            color: var(--primary-color);
        }

        .nav-item i {
            margin-right: 0.75rem;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .welcome {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .logout-btn {
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: var(--primary-hover);
        }

        .section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .card-description {
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .card-button {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .card-button:hover {
            background-color: var(--primary-hover);
        }

        .card-button.secondary {
            background-color: var(--secondary-color);
        }

        .card-button.secondary:hover {
            background-color: var(--secondary-hover);
        }

        .card-button.tertiary {
            background-color: var(--tertiary-color);
        }

        .card-button.tertiary:hover {
            background-color: var(--tertiary-hover);
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="main-content">
            <section class="section">
                
                <h2 class="section-title">💚 Welcome to GradeGenie, <span id="userFirstName"><?php 
    // Safely get the user's first name
    $user_name = isset($_SESSION['user_first_name']) ? $_SESSION['user_first_name'] : 
                (isset($_SESSION['name']) ? $_SESSION['name'] : 'User');
    
    // Get just the first name
    $first_name = explode(" ", $user_name)[0];
    echo htmlspecialchars($first_name); 
?></span>!</h2>
                <p class="card-description">We're so excited to have you here! Thank you for choosing GradeGenie to make grading easier and faster. Follow the simple steps below to get started!</p>
                <div style="position: relative; box-sizing: content-box; max-height: 80vh; max-height: 80svh; width: 100%; aspect-ratio: 2.001389854065323; padding: 40px 0 40px 0;"><iframe src="https://app.supademo.com/embed/clzst4j6k0sc34oytpqccu0wu?embed_v=2" loading="lazy" title="First Time User Flow" allow="clipboard-write" frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe></div>
                
                </section>

            <section class="section">
                <h2 class="section-title">Step 1: Create a Class</h2>
                <div class="card-grid">
                    <div class="card">
                        <h3 class="card-title">Organize Your Classes</h3>
                        <p class="card-description">Begin by setting up your classes to efficiently manage your assignments.</p>
                        <a href="classes.php" class="card-button">Create Your First Class</a>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Step 2: Create a Rubric</h2>
                <div class="card-grid">
                    <div class="card">
                        <h3 class="card-title">Build Custom Rubrics in Seconds</h3>
                        <p class="card-description">Design tailored rubrics to suit your grading needs, or <a href="upload_rubric.php">upload an existing rubric here</a>.</p>
                        <a href="create_rubric.php" class="card-button">Design Your Rubric</a>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Step 3: Create an Assignment</h2>
                <div class="card-grid">
                    <div class="card">
                        <h3 class="card-title">Create Assignments</h3>
                        <p class="card-description">With your class and rubric ready, start creating assignments for your students.</p>
                        <a href="create_assignment.php" class="card-button">Add Your Assignment</a>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Step 4: Upload Submissions to Start Grading</h2>
                <div class="card-grid">
                    <div class="card">
                        <h3 class="card-title">Grade Multiple Assignments at Once</h3>
                        <p class="card-description">Experience the effortlessness and efficiency of automated grading, and provide insightful feedback in seconds.</p>
                        <a href="bulk-grader.php" class="card-button">Start Grading</a>
                    </div>
                </div>
            </section>

            <section class="section">
                <h2 class="section-title">Help & Support</h2>
                <div class="card-grid">
                    <div class="card">
                        <h3 class="card-title">Guided Demos</h3>
                        <p class="card-description">Click through step-by-step, interactive demo walkthroughs to discover how to make the most of GradeGenie.</p>
                        <a href="help.guides.php" class="card-button primary">Access Help Guides</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://kit.fontawesome.com/your-fontawesome-kit-code.js" crossorigin="anonymous"></script>
</body>
</html>
