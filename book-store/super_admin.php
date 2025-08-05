<?php

require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'super_admin') {
    header("Location: admin_login.php");
    exit;
}

// Here processing the book request status
if (isset($_POST['update_request'])) {
    $request_id = $_POST['request_id'] ?? '';
    $status = $_POST['status'] ?? '';

    $stmt = mysqli_prepare($conn, "UPDATE book_requests SET status = ? WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $status, $request_id);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error updating request: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare error: " . mysqli_error($conn);
    }
}

if (isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'] ?? '';

    $stmt = mysqli_prepare($conn, "DELETE FROM book_requests WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $request_id);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error deleting request: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare error: " . mysqli_error($conn);
    }
}

// Handling the new admin account
if (isset($_POST['add_admin'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password_raw = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password_raw)) {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error adding admin: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Prepare error: " . mysqli_error($conn);
        }
    } else {
        echo "All admin fields are required!";
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? '';

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'super_admin'");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            echo "Error deleting user: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Prepare error: " . mysqli_error($conn);
    }
}

// Handling the password reset
if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                echo "Error resetting password: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Prepare error: " . mysqli_error($conn);
        }
    } else {
        echo "New password is required!";
    }
}

// Fetch all requests
$stmt = mysqli_prepare($conn, "SELECT br.id, u.username, b.title, br.category, br.status 
                              FROM book_requests br 
                              JOIN users u ON br.user_id = u.id 
                              JOIN books b ON br.book_id = b.id");
if ($stmt) {
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

// Here we are Fetching the all users
$stmt = mysqli_prepare($conn, "SELECT id, username, email, role FROM users");
if ($stmt) {
    if (mysqli_stmt_execute($stmt)) {
        $users = mysqli_stmt_get_result($stmt);
    } else {
        echo "Error fetching users: " . mysqli_stmt_error($stmt);
        $users = false;
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Prepare error: " . mysqli_error($conn);
    $users = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Panel</title>
    <link rel="stylesheet" href="css/superadminstyle.css">
     
</head>
<body>
    <div class="container">
        <h2>Super Admin Panel</h2>
        <a href="index.php">Logout</a>
        
        <h3>Manage Book Requests</h3>
        <table>
            <tr>
                <th>Username</th>
                <th>Book Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php if ($requests && mysqli_num_rows($requests) > 0) { 
                while ($row = mysqli_fetch_assoc($requests)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $row['category']))); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $row['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $row['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_request">Update</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_request">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } 
            } else { ?>
                <tr>
                    <td colspan="5">No book requests found.</td>
                </tr>
            <?php } ?>
        </table>

        <h3>Add Admin</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_admin">Add Admin</button>
        </form>

        <h3>Manage Users</h3>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php if ($users && mysqli_num_rows($users) > 0) { 
                while ($row = mysqli_fetch_assoc($users)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <input type="password" name="new_password" placeholder="New Password" required>
                                <button type="submit" name="reset_password">Reset Password</button>
                            </form>
                            <?php if ($row['role'] != 'super_admin') { ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_user">Delete</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } 
            } else { ?>
                <tr>
                    <td colspan="4">No users found.</td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>