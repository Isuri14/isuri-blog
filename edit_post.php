<?php
/**
 * edit_post.php — Edit blog post with image update
 * 
 * Features:
 * - Update title and content
 * - Update/replace existing image
 * - Remove image option
 * - Keep old image if no new upload
 * - Delete old image when replacing
 * 
 * @author K.H.I. Hansani
 * @student_id 235043E
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$blog_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($blog_id <= 0) {
    header("Location: index.php");
    exit();
}

$user_id = current_user_id();
$user_message = '';

// Fetch existing blog post
try {
    $stmt = $conn->prepare("SELECT id, user_id, title, content, image FROM blogPost WHERE id = ? AND user_id = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("ii", $blog_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        throw new Exception("Blog not found or no permission to edit.");
    }
    $blog = $res->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    log_error("Edit Blog Load Error (id={$blog_id}, user_id={$user_id}): " . $e->getMessage());
    echo "<p>Something went wrong. Please try again later.</p>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $remove_image = isset($_POST['remove_image']);
    
    $new_image = $blog['image']; // Keep old image by default

    if ($title === '' || $content === '') {
        $user_message = "⚠️ Please provide both title and content.";
    } else {
        // ============================================
        // HANDLE IMAGE REMOVAL
        // ============================================
        if ($remove_image && $blog['image']) {
            $old_image_path = __DIR__ . '/uploads/' . $blog['image'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
            $new_image = null;
        }
        
        // ============================================
        // HANDLE NEW IMAGE UPLOAD
        // ============================================
        if (!$remove_image && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            
            if ($file['error'] === UPLOAD_ERR_OK) {
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $file['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $user_message = "⚠️ Only JPG, PNG, and GIF images are allowed.";
                } else {
                    // Validate file size (max 5MB)
                    $max_size = 5 * 1024 * 1024;
                    if ($file['size'] > $max_size) {
                        $user_message = "⚠️ Image size must be less than 5MB.";
                    } else {
                        // Generate unique filename
                        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $new_image_filename = uniqid('blog_', true) . '.' . $file_extension;
                        
                        $upload_dir = __DIR__ . '/uploads/';
                        $upload_path = $upload_dir . $new_image_filename;
                        
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                            // Delete old image if exists
                            if ($blog['image']) {
                                $old_image_path = $upload_dir . $blog['image'];
                                if (file_exists($old_image_path)) {
                                    unlink($old_image_path);
                                }
                            }
                            $new_image = $new_image_filename;
                        } else {
                            $user_message = "⚠️ Failed to save new image.";
                            log_error("Failed to move uploaded file to: " . $upload_path);
                        }
                    }
                }
            }
        }
        
        // ============================================
        // UPDATE DATABASE
        // ============================================
        if (empty($user_message)) {
            try {
                $update_stmt = $conn->prepare("UPDATE blogPost SET title = ?, content = ?, image = ? WHERE id = ? AND user_id = ?");
                if (!$update_stmt) throw new Exception("Prepare failed: " . $conn->error);
                $update_stmt->bind_param("sssii", $title, $content, $new_image, $blog_id, $user_id);

                if ($update_stmt->execute()) {
                    $update_stmt->close();
                    header("Location: dashboard.php");
                    exit();
                } else {
                    throw new Exception("Execute failed: " . $update_stmt->error);
                }
            } catch (Exception $e) {
                log_error("Edit Blog Update Error (id={$blog_id}, user_id={$user_id}): " . $e->getMessage());
                $user_message = "Something went wrong. Please try again later.";
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
    <title>Edit Blog | BlogWithMe</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container {
            max-width: 800px;
            margin: 100px auto 60px;
            padding: 0 20px;
        }
        h2 { font-size: 1.8em; margin-bottom: 20px; }
        .card {
            background: #ffffff;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .card label {
            display: block;
            margin-top: 12px;
            font-weight: 500;
        }
        .card input[type="text"], 
        .card textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }
        
        /* Current image display */
        .current-image {
            margin-top: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            text-align: center;
        }
        .current-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .current-image-label {
            font-weight: 500;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Image upload styling */
        .image-upload-wrapper {
            margin-top: 12px;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            text-align: center;
            background: #f9f9f9;
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-upload-wrapper:hover {
            border-color: #2d89ef;
            background: #f0f7ff;
        }
        .image-upload-wrapper input[type="file"] { display: none; }
        .upload-label { cursor: pointer; display: block; }
        .upload-icon { font-size: 2em; color: #2d89ef; margin-bottom: 8px; }
        .upload-text { color: #666; font-size: 0.9em; }
        
        /* Image preview */
        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Checkbox styling */
        .checkbox-group {
            margin-top: 15px;
            padding: 12px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 18px;
            margin-top: 15px;
            background-color: #007bff;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn:hover { background-color: #0056b3; }
        .btn.secondary {
            background-color: #6c757d;
        }
        .btn.secondary:hover {
            background-color: #5a6268;
        }
        .notice.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container">
        <h2>Edit Blog Post</h2>

        <?php if ($user_message): ?>
            <div class="notice error"><?= htmlspecialchars($user_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="card">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($blog['title']); ?>" required>

            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="10" required><?= htmlspecialchars($blog['content']); ?></textarea>

            <!-- Show current image if exists -->
            <?php if ($blog['image']): ?>
                <div class="current-image">
                    <span class="current-image-label">Current Image:</span>
                    <img src="uploads/<?= htmlspecialchars($blog['image']); ?>" alt="Current blog image">
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="remove_image" id="removeImage">
                            ❌ Remove this image
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <label>Upload New Image (Optional):</label>
            <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click();">
                <label for="imageInput" class="upload-label">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">
                        Click to upload a new image<br>
                        <small>JPG, PNG, GIF (Max 5MB)</small>
                        <?php if ($blog['image']): ?>
                            <br><small><em>Uploading a new image will replace the current one</em></small>
                        <?php endif; ?>
                    </div>
                </label>
                <input type="file" name="image" id="imageInput" accept="image/*">
            </div>
            
            <!-- New image preview -->
            <div class="image-preview" id="imagePreview" style="display: none;">
                <span style="display: block; margin-bottom: 10px; font-weight: 500;">New Image Preview:</span>
                <img id="previewImg" src="" alt="Preview">
            </div>

            <button type="submit" name="submit" class="btn">Update Blog</button>
            <a href="blog.php?id=<?= $blog_id; ?>" class="btn secondary" style="margin-left: 8px;">Cancel</a>
        </form>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- JavaScript -->
    <script>
    // Image preview functionality
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                
                // Uncheck remove image if user uploads new one
                document.getElementById('removeImage')?.checked = false;
            };
            reader.readAsDataURL(file);
        }
    });

    // Hide new image preview if user checks remove image
    document.getElementById('removeImage')?.addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('imageInput').value = '';
            document.getElementById('imagePreview').style.display = 'none';
        }
    });
    </script>

</body>
</html>