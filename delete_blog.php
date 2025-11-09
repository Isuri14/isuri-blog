<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user_id = current_user_id();
$error_message = '';
$blog = null;

if ($blog_id <= 0) {
    header("Location: index.php");
    exit();
}

// Fetch blog to check ownership
try {
    $stmt = $conn->prepare("SELECT id, title, image FROM blogpost WHERE id = ? AND user_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("ii", $blog_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $error_message = "Blog not found or you don't have permission to delete it.";
    } else {
        $blog = $res->fetch_assoc();
    }
    $stmt->close();
} catch (Exception $e) {
    log_error("Delete Blog Check Error: " . $e->getMessage());
    $error_message = "Something went wrong.";
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $blog) {
    try {
        $del_stmt = $conn->prepare("DELETE FROM blogpost WHERE id = ? AND user_id = ?");
        if (!$del_stmt) throw new Exception("Prepare failed: " . $conn->error);

        $del_stmt->bind_param("ii", $blog_id, $user_id);
        if ($del_stmt->execute()) {
            $del_stmt->close();

            // Delete image file if exists
            if (!empty($blog['image']) && file_exists(__DIR__ . '/uploads/' . $blog['image'])) {
                unlink(__DIR__ . '/uploads/' . $blog['image']);
            }

            $_SESSION['success_message'] = "Blog post deleted successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            throw new Exception("Execute failed: " . $del_stmt->error);
        }
    } catch (Exception $e) {
        log_error("Delete Blog Execute Error: " . $e->getMessage());
        $error_message = "Failed to delete blog post.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Blog | BlogWithMe</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container {
            max-width: 600px;
            margin: 120px auto 60px;
            padding: 0 20px;
        }
        .card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
        }
        .warning-icon {
            font-size: 3em;
            color: #dc3545;
            margin-bottom: 15px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .btn-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn.delete {
            background-color: #dc3545;
            color: #fff;
        }
        .btn.cancel {
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <div class="warning-icon">⚠️</div>
        <h1>Delete Blog Post</h1>

        <?php if (!empty($error_message)): ?>
            <p><?= htmlspecialchars($error_message) ?></p>
            <a href="index.php" class="btn cancel">Go to Home</a>
        <?php elseif ($blog): ?>
            <p>Are you sure you want to delete "<strong><?= htmlspecialchars($blog['title']) ?></strong>"?</p>
            <p>This action cannot be undone.</p>

            <form method="POST">
                <div class="btn-group">
                    <button type="submit" name="confirm_delete" class="btn delete">Yes, Delete</button>
                    <a href="dashboard.php" class="btn cancel">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
