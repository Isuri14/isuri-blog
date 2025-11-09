<?php
/**
 * dashboard.php — User's personal blog dashboard
 */



require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user_id = current_user_id();
$user_message = '';
$my_posts = [];

// Check for success message from actions
if (isset($_SESSION['success_message'])) {
    $user_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

try {
    $sql = "SELECT id, title, content, created_at, updated_at
            FROM blogpost
            WHERE user_id = ?
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        log_error("Dashboard Prepare failed: " . $conn->error);
        $user_message = "Error loading your posts.";
    } else {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $my_posts[] = $row;
            }
        } else {
            log_error("Dashboard Execute failed: " . $stmt->error);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    log_error("Dashboard Exception: " . $e->getMessage());
    $user_message = "Something went wrong.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard - BlogWithMe</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 900px;
    margin: 100px auto 60px;
    padding: 0 20px;
}

h1 {
    text-align: center;
    margin-bottom: 40px;
    color: #333;
}

.success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}

/* Post Cards */
.post-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.2s ease;
}
.post-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.post-card h2 {
    margin-bottom: 10px;
    font-size: 1.5em;
    color: #333;
}
.post-card p {
    color: #555;
    margin-bottom: 10px;
    line-height: 1.5;
}
.post-actions {
    display: flex;
    gap: 10px;
}
.post-actions a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.9em;
    transition: background 0.2s;
}
.post-actions .edit { background: #3b82f6; color: #fff; }
.post-actions .edit:hover { background: #2563eb; }
.post-actions .delete { background: #ef4444; color: #fff; }
.post-actions .delete:hover { background: #b91c1c; }

/* No posts / first post card */
.no-posts {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    color: #555;
    font-size: 1.1em;
    text-align: center;
    margin-top: 20px;
    transition: all 0.2s ease;
}
.no-posts:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}
.no-posts .icon {
    font-size: 3em;
    margin-bottom: 15px;
    color: #3b82f6;
}
.no-posts a {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;
    background-color: #3b82f6;
    color: #fff;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.3s;
}
.no-posts a:hover {
    background-color: #2563eb;
}

/* Responsive */
@media(max-width: 600px) {
    .post-actions {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <h1>My Blog Posts</h1>

    <?php if (!empty($user_message)): ?>
        <div class="success-message"><?= htmlspecialchars($user_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($my_posts)): ?>
        <?php foreach ($my_posts as $post): ?>
            <div class="post-card">
                <h2><?= htmlspecialchars($post['title']); ?></h2>
                <p>
                    Created: <?= date('M d, Y', strtotime($post['created_at'])); ?>
                    <?php if (!empty($post['updated_at'])): ?>
                        | Updated: <?= date('M d, Y', strtotime($post['updated_at'])); ?>
                    <?php endif; ?>
                </p>
                <div class="post-actions">
                    <a href="edit_post.php?id=<?= (int)$post['id']; ?>" class="edit">Edit</a>
                    <a href="delete_blog.php?id=<?= (int)$post['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-posts">
            <div class="icon">📝</div>
            <p>You haven't created any blog posts yet.</p>
            <a href="create_post.php">Create your first post!</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
