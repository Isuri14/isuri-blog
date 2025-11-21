<?php
/**
 * login.php — User login page
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php'; // This loads config.php and starts session
require_once __DIR__ . '/includes/functions.php';

$error_message = '';
$old_email = '';

// If already logged in, redirect to dashboard/home
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($old_email === '' || $password === '') {
        $error_message = "⚠️ Please enter both email and password.";
    } else {
        $sql = "SELECT id, username, password FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            log_error("Login Prepare failed: " . $conn->error . " | SQL: " . $sql);
            $error_message = "Something went wrong. Please try again later.";
        } else {
            try {
                $stmt->bind_param("s", $old_email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        // Clear any old session data
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['LAST_ACTIVITY'] = time();
                        
                        // Force session write
                        session_write_close();
                        
                        // Redirect
                        header("Location: index.php");
                        exit();
                    } else {
                        $error_message = "Invalid email or password.";
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
                $stmt->close();
            } catch (Exception $e) {
                log_error("Login Exception: " . $e->getMessage());
                $error_message = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - BlogWithMe</title>
<style>
body { font-family: Arial, sans-serif; background: #f8f9fa; margin:0; padding:0; }
.container { max-width: 400px; margin: 100px auto; padding: 0 20px; }
.card { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
h1 { text-align: center; margin-bottom: 20px; }
.user-message {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    text-align: center;
}
input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1em;
    box-sizing: border-box;
}
label {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
}
button.btn {
    width: 100%;
    padding: 12px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 1em;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
button.btn:hover {
    background: #2563eb;
}
p { text-align: center; font-size: 0.95em; margin-top: 20px; }
p a { color: #3b82f6; text-decoration: none; font-weight: 500; }
p a:hover { text-decoration: underline; }
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h1>Login</h1>

        <?php if ($error_message): ?>
            <div class="user-message"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old_email); ?>" required autocomplete="email">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit" class="btn">Login</button>
        </form>

        <p>
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
