<?php
session_start();
require 'db_connect.php';
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Fetching the dashboard related stuff
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT id) as count FROM users WHERE role='user'"))['count'];
$total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_requests"))['count'];
$in_progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_requests WHERE status='in_progress'"))['count'];
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_requests WHERE status='completed'"))['count'];

// Fetching all requests
$requests = mysqli_query($conn, "SELECT br.id, u.username, b.title, br.category, br.status 
                                 FROM book_requests br 
                                 JOIN users u ON br.user_id = u.id 
                                 JOIN books b ON br.book_id = b.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <a href="index.php">Logout</a>
        <h3>Statistics</h3>
        <p>Total Users: <?php echo $total_users; ?></p>
        <p>Total Book Requests: <?php echo $total_requests; ?></p>
        <p>Requests In Progress: <?php echo $in_progress; ?></p>
        <p>Completed Requests: <?php echo $completed; ?></p>
        <h3>All Book Requests</h3>
        <table>
            <tr>
                <th>Username</th>
                <th>Book Title</th>
                <th>Category</th>
                <th>Status</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($requests)) { ?>
                <tr>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo ucfirst(str_replace('_', ' ', $row['category'])); ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>