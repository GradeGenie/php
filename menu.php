<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default values
$user_logged_in = isset($_SESSION['user_id']);
$has_active_subscription = false;

// If user is logged in after Stripe checkout, assume they have an active subscription
if ($user_logged_in && isset($_SESSION['stripe_session_id'])) {
    $has_active_subscription = true;
    error_log('User has active subscription from Stripe session: ' . $_SESSION['stripe_session_id']);
}

// Try database connection with error handling
try {
    // Include database connection only if not already included
    if (!defined('DB_INCLUDED')) {
        include 'api/c.php'; // Include the database connection
        define('DB_INCLUDED', true);
    }

    // Only check subscription in database if we don't already know it's active
    if ($user_logged_in && !$has_active_subscription && isset($conn) && !isset($conn->connect_error)) {
        $user_id = $_SESSION['user_id'];
        // Prepare and execute the SQL query to check subscription status
        $query = "SELECT active_sub FROM users WHERE uid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $has_active_subscription = ($row['active_sub'] == 1);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    // Log error but continue
    error_log('Error in menu.php: ' . $e->getMessage());

    // If user is logged in, assume they have an active subscription
    if ($user_logged_in) {
        $has_active_subscription = true;
    }
}

// Redirect to upgrade_plan.php if no active subscription
function is_upgrade_plan_page()
{
    return basename($_SERVER['PHP_SELF']) === 'upgrade_plan.php';
}

if ($user_logged_in && !$has_active_subscription && !is_upgrade_plan_page()) {
    header('Location: upgrade_plan.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="apple-touch-icon" href="https://app.getgradegenie.com/assets/apple-touch-icon.png" sizes="180x180"
        type="image/png">
    <link rel="icon" href="https://app.getgradegenie.com/assets/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="https://app.getgradegenie.com/assets/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
</head>

<body>
    <nav>
        <div id="menuToggle" class="toggle-icon" >
            <i id="toggleArrow" class="fas fa-angle-left"></i>
        </div>

        <div id="navItems">
            <a href="index.php" class="navItem">
                <i class="navIcon fas fa-home"></i>
                <div class="navLabel">Home</div>
            </a>
            <?php if ($user_logged_in && $has_active_subscription): ?>
                <a href="classes.php" class="navItem">
                    <i class="navIcon fas fa-folder"></i>
                    <div class="navLabel">My Classes</div>
                </a>
                <a href="create_rubric.php" class="navItem">
                    <i class="navIcon fas fa-pencil-ruler"></i>
                    <div class="navLabel">Rubric Creator</div>
                </a>
                <a href="view_rubrics.php" class="navItem">
                    <i class="navIcon fas fa-layer-group"></i>
                    <div class="navLabel">My Rubrics</div>
                </a>
                <a href="create_assignment.php" class="navItem">
                    <i class="navIcon fas fa-clipboard-list"></i>
                    <div class="navLabel">My Assignment</div>
                </a>
                <a href="bulk-grader.php" class="navItem">
                    <i class="navIcon fas fa-tasks"></i>
                    <div class="navLabel">Instant Grader</div>
                </a>
                <a href="scan.php" class="navItem">
                    <i class="navIcon fas fa-check-circle"></i>
                    <div class="navLabel">Paper Scanner</div>
                </a>
            <?php endif; ?>
            <a href="help.guides.php" class="navItem">
                <i class="navIcon fas fa-book"></i>
                <div class="navLabel">Help Guides</div>
            </a>



            <a href="https://gradegenie.hipporello.net/desk" class="navItem">
                <i class="navIcon fas fa-lightbulb"></i>
                <div class="navLabel">Feedback</div>
            </a>

            <a href="mailto:?cc=hello@getgradegenie.com&subject=Don’t%20Miss%20Out%20on%20Easier%2C%20100x%20Faster%20Grading!&body=Hi%20%5BFriend's%20Name%5D%2C%0D%0A%0D%0AI%20hope%20you%27re%20doing%20well!%20I%20wanted%20to%20let%20you%20know%20about%20an%20incredible%20tool%20I%27ve%20been%20using%20lately%20called%20GradeGenie%20%E2%80%93%20it%27s%20completely%20transformed%20the%20way%20I%20grade%20assignments.%20It%27s%20helped%20me%20grade%20over%20100x%20faster%20while%20keeping%20everything%20consistent%2C%20accurate%2C%20and%20so%20much%20easier.%0D%0A%0D%0ABefore%20GradeGenie%2C%20grading%20was%20such%20a%20time-consuming%20and%20frustrating%20process%2C%20but%20now%2C%20I%27ve%20been%20able%20to%20free%20up%20hours%20every%20week.%20It%27s%20seriously%20a%20game-changer%2C%20and%20I%20know%20you%27ll%20appreciate%20how%20much%20time%20it%20can%20save%20you%20too!%0D%0A%0D%0AYou%20can%20check%20it%20out%20at%20getgradegenie.com%20or%20sign%20up%20directly%20at%20https%3A%2F%2Fapp.getgradegenie.com%2Fsignup.php%20%E2%80%93%20trust%20me%2C%20you%27ll%20wish%20you%20had%20started%20using%20it%20sooner!%0D%0A%0D%0AHere%27s%20the%20best%20part%20%E2%80%93%20if%20you%20sign%20up%20and%20mention%20my%20name%2C%20we%27ll%20both%20get%2020%25%20off%20our%20next%20month.%20It%27s%20that%20simple%20and%20so%20worth%20it!%0D%0A%0D%0ALet%20me%20know%20what%20you%20think!%0D%0A%0D%0AWarm%20regards%2C%0D%0A%5BYour%20Name%5D%0D%0A
" class="navItem">
                <i class="navIcon fas fa-hand-holding-usd"></i>
                <div class="navLabel">Earn Rewards</div>
            </a>

        </div>
    </nav>

    <?php if (!$user_logged_in): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            $(document).ready(function () {
                showRegistrationModal();
            });
        </script>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('menuToggle');
            const nav = document.querySelector('nav');
            const body = document.body;

            toggleBtn.addEventListener('click', function () {
                nav.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        });
    </script>

</body>

</html>