<?php

require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


try {

    if (isset($_POST['register'])) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password_raw = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($username) || empty($email) || empty($password_raw) || empty($role)) {
            echo "All registration fields are required!";
        } elseif (!in_array($role, ['user', 'admin', 'super_admin'])) {
            echo "Invalid role selected!";
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);

            // Prepare the statement
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                // Bind parameters
                mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password, $role);

                if (mysqli_stmt_execute($stmt)) {
                    echo "Registration successful! <a href='login.php'>Log in here</a>.";
                } else {
                    echo "Registration error: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo "Prepare error: " . mysqli_error($conn);
            }
        }
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Book Request System</title>
    <link rel="stylesheet" href="css/signupstyle.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="" disabled selected>Select Role</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <button type="submit" name="register">Register</button>
        </form>
        <p>Already have an account? <a href="index.php">Login here</a>.</p>
    </div>
</body>
</html>