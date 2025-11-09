<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$posts = [];

if ($search_query !== '') {
    $like_term = "%$search_query%";
    $stmt = $conn->prepare("
        SELECT b.id, b.title, b.content, b.created_at, u.username
        FROM blogpost b
        JOIN user u ON b.user_id = u.id
        WHERE b.title LIKE ? OR b.content LIKE ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param("ss", $like_term, $like_term);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search results for "<?= htmlspecialchars($search_query) ?>"</title>
<link rel="stylesheet" href="assets/css/styles.css">
<style>
.container { max-width: 900px; margin: 100px auto 60px; padding: 0 20px; }
.post-card { background:#fff; padding:20px; border-radius:10px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.post-card h2 { margin-bottom:10px; font-size:1.5em; }
.post-card p { color:#555; line-height:1.5; }
</style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <h1>Search Results for "<?= htmlspecialchars($search_query) ?>"</h1>

    <?php if (empty($posts)): ?>
        <p>No blog posts found matching your query.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <h2><a href="view_post.php?id=<?= $post['id']; ?>"><?= htmlspecialchars($post['title']); ?></a></h2>
                <p>By <?= htmlspecialchars($post['username']); ?> | <?= date('M d, Y', strtotime($post['created_at'])); ?></p>
                <p><?= nl2br(substr(htmlspecialchars($post['content']),0,200)); ?>...</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
