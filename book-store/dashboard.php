<?php

require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handling the request cancellation
if (isset($_POST['cancel_request'])) {
    $request_id = $_POST['request_id'] ?? '';

    // query for deleting a book request
    $stmt = mysqli_prepare($conn, "DELETE FROM book_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error canceling request: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare error: " . mysqli_error($conn);
    }
}

// query for the book requests
$stmt = mysqli_prepare($conn, "SELECT br.id, br.status, br.category, b.title, b.author 
                              FROM book_requests br 
                              JOIN books b ON br.book_id = b.id 
                              WHERE br.user_id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $requests = mysqli_stmt_get_result($stmt);
    } else {
        echo "Error fetching requests: " . mysqli_stmt_error($stmt);
        $requests = false;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Prepare error: " . mysqli_error($conn);
    $requests = false;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="script.js"></script>
    
</head>

<body>
    <div class="container">
        <h2>User Dashboard</h2>
        <a href="request_book.php">Request a Book</a> | <a href="index.php">Logout</a>
        <h3>Your Book Requests</h3>
        <table>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if ($requests && mysqli_num_rows($requests) > 0) {
                while ($row = mysqli_fetch_assoc($requests)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['category']))); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'pending') { ?>
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="cancel_request">Cancel</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5">No book requests found.</td>
                </tr>
            <?php } ?>
        </table>
        <div id="notification">
            <?php
            // Fetch the latest book request
            $stmt = mysqli_prepare($conn, "SELECT b.title, br.status 
                                          FROM book_requests br 
                                          JOIN books b ON br.book_id = b.id 
                                          WHERE br.user_id = ? 
                                          ORDER BY br.created_at DESC LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    $latest_request = mysqli_fetch_assoc($result);
                    if ($latest_request) {
                        echo "Your request for " . htmlspecialchars($latest_request['title']) . " is " . htmlspecialchars($latest_request['status']) . ".";
                    }
                } else {
                    echo "Error fetching latest request: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Prepare error: " . mysqli_error($conn);
            }
            ?>
        </div>
    </div>
</body>

</html>