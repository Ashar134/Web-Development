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

// Prepare statement to fetch user information
$stmt = mysqli_prepare($conn, "SELECT username, email FROM users WHERE id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        if (!$user) {
            echo "Error: User not found!";
            exit;
        }
    } else {
        echo "Error fetching user: " . mysqli_stmt_error($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Prepare error: " . mysqli_error($conn);
    exit;
}

if (isset($_POST['submit'])) {
    $book_id = $_POST['book_id'] ?? '';
    $category = $_POST['category'] ?? '';
    $file_path = '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_path = 'Uploads/' . basename($_FILES['file']['name']);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            echo "Error uploading file!";
            $file_path = '';
        }
    }

    // Prepare statement to insert book request
    $stmt = mysqli_prepare($conn, "INSERT INTO book_requests (user_id, book_id, category, file_path) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiss", $user_id, $book_id, $category, $file_path);
        if (mysqli_stmt_execute($stmt)) {
            echo "Request submitted!";
        } else {
            echo "Error: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request a Book</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Request a Book</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" disabled>
            <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
            <select name="category" required>
                <option value="app_development">App Development</option>
                <option value="mobile_development">Mobile Development</option>
                <option value="ai">AI</option>
            </select>
            <select name="book_id" required>
                <?php
                $query = "SELECT id, title FROM books";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='{$row['id']}'>" . htmlspecialchars($row['title']) . "</option>";
                }
                ?>
            </select>
            <input type="file" name="file">
            <button type="submit" name="submit">Submit Request</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>