<?php 
date_default_timezone_set('Asia/Colombo'); // Set your local timezone

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$logged_in = function_exists('is_logged_in') && is_logged_in();
$current_user_id = $logged_in ? current_user_id() : 0;

$post_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($post_id <= 0) { echo "<p>Invalid post ID.</p>"; exit(); }

// --- Helper function for "time ago"
function time_elapsed_string($datetime, $full = false) {
    // Treat datetime from MySQL as UTC
    $ago = new DateTime($datetime, new DateTimeZone('UTC'));
    // Convert to local timezone
    $ago->setTimezone(new DateTimeZone('Asia/Colombo'));

    $now = new DateTime('now', new DateTimeZone('Asia/Colombo'));
    $diff = $now->diff($ago);

    $weeks = floor($diff->days / 7);
    $days = $diff->days - ($weeks * 7);

    $values = [
        'yr' => $diff->y,
        'month' => $diff->m,
        'week' => $weeks,
        'day' => $days,
        'hr' => $diff->h,
        'min' => $diff->i,
        'second' => $diff->s,
    ];

    if ($diff->days == 0 && $diff->h == 0 && $diff->i == 0 && $diff->s < 5) {
        return 'just now';
    }

    $string = [];
    foreach ($values as $k => $v) {
        if ($v) $string[] = $v . ' ' . $k . ($v > 1 ? 's' : '');
    }

    if (!$full) $string = array_slice($string, 0, 1);

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

// --- Handle Like Toggle
if ($logged_in && isset($_POST['toggle_like'])) {
    $check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $check->bind_param("ii", $current_user_id, $post_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $del->bind_param("ii", $current_user_id, $post_id);
        $del->execute();
        $del->close();
    } else {
        $ins = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $ins->bind_param("ii", $current_user_id, $post_id);
        $ins->execute();
        $ins->close();
    }
    $check->close();
    header("Location: view_post.php?id=$post_id"); exit();
}

// --- Handle Add Comment
if ($logged_in && isset($_POST['add_comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $current_user_id, $post_id, $comment);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: view_post.php?id=$post_id"); exit();
}

// --- Handle Delete Comment
if ($logged_in && isset($_POST['delete_comment'])) {
    $comment_id = (int) $_POST['comment_id'];

    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->bind_result($comment_user_id);
    $stmt->fetch();
    $stmt->close();

    if ($comment_user_id == $current_user_id) {
        $del = $conn->prepare("DELETE FROM comments WHERE id=?");
        $del->bind_param("i", $comment_id);
        $del->execute();
        $del->close();
    }
    header("Location: view_post.php?id=$post_id"); exit();
}

// --- Fetch Post
$stmt = $conn->prepare("
    SELECT b.id, b.user_id, b.title, b.content, b.image, b.created_at, u.username
    FROM blogpost b
    JOIN user u ON b.user_id = u.id
    WHERE b.id=? LIMIT 1
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo "<p>Post not found.</p>"; exit(); }
$post = $res->fetch_assoc();
$stmt->close();

// --- Fetch Likes
$like_count = 0; $user_liked = false;
$stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id=?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($like_count);
$stmt->fetch();
$stmt->close();

if ($logged_in) {
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
    $stmt->bind_param("ii", $current_user_id, $post_id);
    $stmt->execute();
    $stmt->store_result();
    $user_liked = $stmt->num_rows > 0;
    $stmt->close();
}

// --- Fetch Comments
$comments = [];
$stmt = $conn->prepare("
    SELECT c.id, c.comment, c.created_at, c.user_id, u.username
    FROM comments c
    JOIN user u ON c.user_id=u.id
    WHERE c.post_id=? ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $comments[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($post['title']); ?> | BlogWithMe</title>
<link rel="stylesheet" href="assets/css/styles.css">
<style>
body { background:#f3f4f6; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#333; }
.container { max-width:800px; margin:80px auto; padding:0 15px; }
.card { background:#fff; border-radius:16px; padding:30px; box-shadow:0 10px 25px rgba(0,0,0,0.08); }
h1 { font-size:2.2rem; margin-bottom:10px; }
.meta { font-size:0.9rem; color:#777; margin-bottom:25px; }
.blog-image-wrapper { margin:25px 0; text-align:center; }
.blog-image { max-width:100%; border-radius:12px; }
.post-content { line-height:1.75; font-size:1.05rem; margin-bottom:30px; }
.like-section { display:flex; align-items:center; gap:15px; margin-bottom:35px; }
.like-btn { background:#eee; border:none; border-radius:50px; padding:10px 20px; font-weight:600; cursor:pointer; transition: all .3s; display:flex; align-items:center; gap:8px; }
.like-btn:hover { background:#ddd; }
.like-btn.liked { background:#e63946; color:#fff; }
.like-count { font-size:0.95rem; color:#555; }
.comments h3 { font-size:1.3rem; margin-bottom:15px; }
.comment-form textarea { width:100%; height:80px; padding:12px 15px; border-radius:12px; border:1px solid #ccc; resize:none; font-size:1rem; }
.comment-form button { margin-top:10px; padding:10px 20px; border:none; border-radius:12px; background:#3b82f6; color:#fff; font-weight:600; cursor:pointer; transition:0.3s; }
.comment-form button:hover { background:#2563eb; }
.comment-box { background:#f9f9f9; padding:15px 20px; border-radius:12px; margin-bottom:15px; box-shadow:0 3px 8px rgba(0,0,0,0.05); position:relative; }
.comment-author { font-weight:600; color:#111; }
.comment-date { font-size:0.85rem; color:#777; margin-left:6px; }
.comment-box p { margin-top:5px; line-height:1.5; color:#333; }
.comment-delete-btn { position:absolute; top:10px; right:10px; background:none; border:none; color:#dc2626; cursor:pointer; font-size:1rem; }
.btn { display:inline-block; padding:10px 18px; border-radius:10px; background:#007bff; color:#fff; text-decoration:none; font-weight:500; transition:0.3s; }
.btn:hover { background:#0056b3; }
.btn.danger { background:#dc3545; }
.btn.danger:hover { background:#a71d2a; }
a.back-link { display:inline-block; margin-top:25px; color:#007bff; text-decoration:none; }
a.back-link:hover { text-decoration:underline; }
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="card">
        <h1><?= htmlspecialchars($post['title']); ?></h1>
        <p class="meta">👤 <?= htmlspecialchars($post['username']); ?> | 📅 <?= time_elapsed_string($post['created_at']); ?></p>

        <?php if($post['image']): ?>
            <div class="blog-image-wrapper"><img src="uploads/<?= htmlspecialchars($post['image']); ?>" class="blog-image"></div>
        <?php endif; ?>

        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])); ?></div>

        <?php if($logged_in): ?>
        <form method="POST" class="like-section">
            <button type="submit" name="toggle_like" class="like-btn <?= $user_liked?'liked':'' ?>">
                <?= $user_liked ? '❤️ Liked' : '🤍 Like' ?>
            </button>
            <span class="like-count"><?= $like_count ?> <?= $like_count ==1 ? 'like':'likes' ?></span>
        </form>
        <?php else: ?>
            <p><a href="login.php">Login</a> to like this post.</p>
        <?php endif; ?>

        <div class="comments">
            <h3>Comments (<?= count($comments); ?>)</h3>
            <?php if($logged_in): ?>
            <form method="POST" class="comment-form">
                <textarea name="comment" placeholder="Write a comment..." required></textarea>
                <button type="submit" name="add_comment">Post Comment</button>
            </form>
            <?php else: ?>
                <p><a href="login.php">Login</a> to comment.</p>
            <?php endif; ?>

            <?php if(empty($comments)): ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach($comments as $c): ?>
                <div class="comment-box">
                    <span class="comment-author"><?= htmlspecialchars($c['username']); ?></span>
                    <span class="comment-date"><?= time_elapsed_string($c['created_at']); ?></span>
                    <?php if($logged_in && $current_user_id==$c['user_id']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $c['id']; ?>">
                            <button type="submit" name="delete_comment" class="comment-delete-btn" title="Delete comment">🗑️</button>
                        </form>
                    <?php endif; ?>
                    <p><?= nl2br(htmlspecialchars($c['comment'])); ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <hr>
        <?php if($logged_in && $current_user_id==$post['user_id']): ?>
            <a href="edit_post.php?id=<?= $post['id']; ?>" class="btn">✏️ Edit</a>
            <a href="delete_blog.php?id=<?= $post['id']; ?>" class="btn danger" onclick="return confirm('Are you sure?');">🗑️ Delete</a>
        <?php endif; ?>

        <a href="index.php" class="back-link">← Back to All Posts</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
