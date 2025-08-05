<?php
// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$message = ''; // Feedback message for login

try {
    if (isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password_input = $_POST['password'] ?? '';

        if (empty($username) || empty($password_input)) {
            $message = "All login fields are required";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($result) === 0) {
                        $message = "User not found!";
                    } else {
                        $row = mysqli_fetch_assoc($result);
                        $hashed_password = $row['password'] ?? '';

                        if (empty($hashed_password)) {
                            $message = "No password set for this user.";
                        } elseif (password_verify($password_input, $hashed_password)) {
                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['role'] = $row['role'];

                            if ($row['role'] === 'admin') {
                                header("Location: admin_dashboard.php");
                            } elseif ($row['role'] === 'super_admin') {
                                header("Location: super_admin.php");
                            } else {
                                header("Location: dashboard.php");
                            }
                            exit();
                        } else {
                            $message = "Invalid password!";
                        }
                    }
                } else {
                    $message = "Query error: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $message = "Prepare error: " . mysqli_error($conn);
            }
        }
    }
} catch (Exception $e) {
    $message = "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/signupstyle.css">
</head>

<body>
    <div class="container">
        <h2>Login</h2>


        <?php if (!empty($message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>

</html>