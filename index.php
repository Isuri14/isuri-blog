<?php
/**
 * index.php — Public landing page / Blog listing
 * 4-column professional grid layout with likes
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_message = '';
$posts = [];

$blog_table = "blogpost";

try {
    $sql = "
        SELECT bp.id, bp.title, bp.content, bp.created_at, bp.image, u.username,
               (SELECT COUNT(*) FROM likes l WHERE l.post_id = bp.id) AS like_count
        FROM {$blog_table} bp
        JOIN user u ON bp.user_id = u.id
        ORDER BY bp.created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        log_error("Index Prepare failed: " . $conn->error);
        $user_message = "Error loading posts.";
    } else {
        if (!$stmt->execute()) {
            log_error("Index Execute failed: " . $stmt->error);
            $user_message = "Error loading posts.";
        } else {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $posts[] = $row;
                }
            } else {
                $user_message = "No blog posts found.";
            }
        }
        $stmt->close();
    }
} catch (Exception $e) {
    log_error("Index Exception: " . $e->getMessage());
    $user_message = "Something went wrong.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BlogWithMe</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 1200px;
    margin: 100px auto 60px;
    padding: 0 20px;
}
.hero {
    text-align: center;
    margin-bottom: 40px;
}
.hero h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
}
.hero p {
    font-size: 1.2em;
    color: #666;
}
/* Grid */
.post-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}
/* Blog Card */
.post-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}
.post-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.post-card h2 {
    font-size: 1.4em;
    margin: 16px;
    flex-shrink: 0;
}
.post-card h2 a {
    text-decoration: none;
    color: #007bff;
    transition: color 0.2s;
}
.post-card h2 a:hover {
    color: #0056b3;
    text-decoration: underline;
}
.post-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
}
.post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.post-excerpt {
    padding: 0 16px;
    margin: 12px 0;
    color: #555;
    line-height: 1.5;
    word-wrap: break-word;
    min-height: 60px;
}
/* Footer */
.post-footer {
    padding: 0 16px 16px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.post-footer .meta {
    font-size: 0.85em;
    color: #888;
}
.post-footer .likes {
    font-size: 0.9em;
    color: #e63946;
    display: flex;
    align-items: center;
    gap: 5px;
}
.post-footer .btn {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9em;
    transition: background 0.2s;
}
.post-footer .btn:hover {
    background-color: #0056b3;
}
/* Responsive */
@media(max-width: 1024px) {
    .post-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}
@media(max-width: 768px) {
    .post-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    .post-image {
        height: 150px;
    }
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container">
    <div class="hero">
        <h1>Welcome to BlogWithMe</h1>
        <p>Share your thoughts with the world</p>
    </div>

    <?php if (!empty($user_message)): ?>
        <p style="text-align:center; color:#555; margin-bottom:20px;"><?= htmlspecialchars($user_message); ?></p>
    <?php endif; ?>

    <div class="post-grid">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $row): ?>
                <div class="post-card">
                    <?php if (!empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])): ?>
                        <div class="post-image">
                            <a href="view_post.php?id=<?= (int)$row['id']; ?>">
                                <img src="uploads/<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['title']); ?>">
                            </a>
                        </div>
                    <?php endif; ?>
                    <h2><a href="view_post.php?id=<?= (int)$row['id']; ?>"><?= htmlspecialchars($row['title']); ?></a></h2>
                    <p class="post-excerpt">
                        <?= htmlspecialchars(mb_strimwidth($row['content'], 0, 120, '...')); ?>
                    </p>
                    <div class="post-footer">
                        <span class="meta"><?= htmlspecialchars($row['username']); ?> • <?= date('M d, Y', strtotime($row['created_at'])); ?></span>
                        <span class="likes">❤️ <?= (int)$row['like_count']; ?></span>
                        <a href="view_post.php?id=<?= (int)$row['id']; ?>" class="btn">See More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
