<?php
/**
 * register.php — Modern User Registration
 */

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$error_message = '';
$success_message = '';
$old_username = '';
$old_email = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_username = trim($_POST['username'] ?? '');
    $old_email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($old_username) || empty($old_email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($old_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        try {
            // Check for duplicate email
            $check_stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
            $check_stmt->bind_param("s", $old_email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $error_message = "Email already registered. Please login.";
                $check_stmt->close();
            } else {
                $check_stmt->close();

                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                $insert_stmt = $conn->prepare("INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("ssss", $old_username, $old_email, $hashed_password, $role);

                if ($insert_stmt->execute()) {
                    $success_message = "Registration successful! You can now login.";
                    $old_username = '';
                    $old_email = '';
                } else {
                    log_error("Register Insert failed: " . $insert_stmt->error);
                    $error_message = "Registration failed. Please try again.";
                }
                $insert_stmt->close();
            }
        } catch (Exception $e) {
            log_error("Register Exception: " . $e->getMessage());
            $error_message = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BlogWithMe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; margin:0; padding:0; }
        .container { max-width: 450px; margin: 100px auto 60px; padding: 0 20px; }
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { text-align: center; color: #1f2937; margin-bottom: 20px; }
        .user-message { padding: 12px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-weight:600; }
        .user-message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .user-message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 1em; }
        button { width: 100%; padding: 12px; background: #3b82f6; color: #fff; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #2563eb; }
        .login-link { text-align: center; margin-top: 15px; color: #4b5563; }
        .login-link a { color: #3b82f6; text-decoration: none; font-weight:600; }
        .login-link a:hover { text-decoration: underline; }
        @media(max-width:500px){ .container{margin:70px 15px 40px;} }
    </style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>Create Account</h2>

        <?php if(!empty($error_message)): ?>
            <div class="user-message error"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if(!empty($success_message)): ?>
            <div class="user-message success"><?= htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($old_username); ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old_email); ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
