<?php
session_start();
include '../api/c.php';

// Check if the user is authorized
if (!isset($_SESSION['user_email']) || strpos($_SESSION['user_email'], 'getgradegenie') === false) {
    header('Location: ../index.php');
    exit();
}

// Function to format date
function formatDate($date) {
    return date('j M \'y, g:ia', strtotime($date));
}

// Fetch active subscribers
$activeQuery = "SELECT uid, name, email, join_date, last_login, stripeID FROM users WHERE active_sub = 1";
$activeResult = $conn->query($activeQuery);

// Fetch inactive subscribers
$inactiveQuery = "SELECT uid, name, email, join_date, last_login, stripeID FROM users WHERE active_sub IS NULL OR active_sub != 1";
$inactiveResult = $conn->query($inactiveQuery);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Albert Sans', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            display: flex;
            justify-content: space-between;
        }
        .table-container {
            width: 48%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h2 {
            color: #333;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <div class="container">
        <div class="table-container">
            <h2>Active Subscribers</h2>
            <table>
                <tr>
                    <th>UID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Join Date</th>
                    <th>Last Login</th>
                    <th>Stripe ID</th>
                </tr>
                <?php while ($row = $activeResult->fetch_assoc()): ?>
                <tr>
                    <td><a href="view_customer.php?id=<?php echo $row['uid']; ?>"><?php echo $row['uid']; ?></a></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo formatDate($row['join_date']); ?></td>
                    <td><?php echo $row['last_login'] ? formatDate($row['last_login']) : 'N/A'; ?></td>
                    <td><a href="https://dashboard.stripe.com/customers/<?php echo $row['stripeID']; ?>" target="_blank"><?php echo $row['stripeID']; ?></a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="table-container">
            <h2>Inactive Subscribers</h2>
            <table>
                <tr>
                    <th>UID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Join Date</th>
                    <th>Last Login</th>
                    <th>Stripe ID</th>
                </tr>
                <?php while ($row = $inactiveResult->fetch_assoc()): ?>
                <tr>
                    <td><a href="view_customer.php?id=<?php echo $row['uid']; ?>"><?php echo $row['uid']; ?></a></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo formatDate($row['join_date']); ?></td>
                    <td><?php echo $row['last_login'] ? formatDate($row['last_login']) : 'N/A'; ?></td>
                    <td><?php echo $row['stripeID'] ? "<a href='https://dashboard.stripe.com/customers/{$row['stripeID']}' target='_blank'>{$row['stripeID']}</a>" : 'N/A'; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>
