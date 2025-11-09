<?php
/**
 * add_blog.php — Create a new blog post with image upload
 * 
 * Features:
 * - Text content (title + content)
 * - Image upload (JPG, JPEG, PNG, GIF)
 * - Image validation (size, type, dimensions)
 * - Secure file naming
 * - Error handling
 * 
 * @author K.H.I. Hansani
 * @student_id 235043E
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login(); // Only logged-in users

$user_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = current_user_id();
    
    // Initialize image variable
    $image_filename = null;

    // Validate text fields
    if ($title === '' || $content === '') {
        $user_message = "⚠️ Please provide both title and content.";
    } else {
        // ============================================
        // HANDLE IMAGE UPLOAD
        // ============================================
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $user_message = "⚠️ Error uploading image. Please try again.";
                log_error("Image upload error code: " . $file['error']);
            } else {
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = $file['type'];
                
                if (!in_array($file_type, $allowed_types)) {
                    $user_message = "⚠️ Only JPG, PNG, and GIF images are allowed.";
                } else {
                    // Validate file size (max 5MB)
                    $max_size = 5 * 1024 * 1024; // 5MB in bytes
                    if ($file['size'] > $max_size) {
                        $user_message = "⚠️ Image size must be less than 5MB.";
                    } else {
                        // Get file extension
                        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        
                        // Generate unique filename to prevent overwriting
                        $image_filename = uniqid('blog_', true) . '.' . $file_extension;
                        
                        // Define upload path
                        $upload_dir = __DIR__ . '/uploads/';
                        $upload_path = $upload_dir . $image_filename;
                        
                        // Create uploads directory if it doesn't exist
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // Move uploaded file
                        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                            $user_message = "⚠️ Failed to save image. Please try again.";
                            log_error("Failed to move uploaded file to: " . $upload_path);
                            $image_filename = null;
                        }
                    }
                }
            }
        }
        
        // ============================================
        // INSERT BLOG POST TO DATABASE
        // ============================================
        if (empty($user_message)) {
            try {
                $stmt = $conn->prepare("INSERT INTO blogpost (user_id, title, content, image) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    log_error("Add Blog Prepare failed (user_id={$user_id}): " . $conn->error);
                    $user_message = "Something went wrong. Please try again later.";
                } else {
                    $stmt->bind_param("isss", $user_id, $title, $content, $image_filename);
                    if ($stmt->execute()) {
                        $blog_id = $conn->insert_id;
                        $stmt->close();
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        log_error("Add Blog Execute failed (user_id={$user_id}): " . $stmt->error);
                        $user_message = "Something went wrong. Please try again later.";
                        $stmt->close();
                        
                        // Delete uploaded image if database insert fails
                        if ($image_filename && file_exists($upload_dir . $image_filename)) {
                            unlink($upload_dir . $image_filename);
                        }
                    }
                }
            } catch (mysqli_sql_exception $e) {
                log_error("Add Blog Exception (user_id={$user_id}): " . $e->getMessage());
                $user_message = "Something went wrong. Please try again later.";
            } catch (Exception $e) {
                log_error("Add Blog General Exception (user_id={$user_id}): " . $e->getMessage());
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
    <title>Create New Blog | BlogWithMe</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .container {
            max-width: 800px;
            margin: 100px auto 60px;
            padding: 0 20px;
        }
        h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
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
        .image-upload-wrapper input[type="file"] {
            display: none;
        }
        .upload-label {
            cursor: pointer;
            display: block;
        }
        .upload-icon {
            font-size: 2em;
            color: #2d89ef;
            margin-bottom: 8px;
        }
        .upload-text {
            color: #666;
            font-size: 0.9em;
        }
        
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
        .remove-image {
            display: inline-block;
            margin-top: 10px;
            color: #dc3545;
            cursor: pointer;
            font-size: 0.9em;
        }
        .remove-image:hover {
            text-decoration: underline;
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
        .btn:hover {
            background-color: #0056b3;
        }
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
        <h2>Create a New Blog Post</h2>

        <?php if (!empty($user_message)): ?>
            <div class="notice error"><?= htmlspecialchars($user_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" class="card">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">

            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="10" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>

            <label>Image (Optional):</label>
            <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click();">
                <label for="imageInput" class="upload-label">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">
                        Click to upload an image<br>
                        <small>JPG, PNG, GIF (Max 5MB)</small>
                    </div>
                </label>
                <input type="file" name="image" id="imageInput" accept="image/*">
            </div>
            
            <!-- Image Preview -->
            <div class="image-preview" id="imagePreview" style="display: none;">
                <img id="previewImg" src="" alt="Preview">
                <div class="remove-image" onclick="removeImage()">❌ Remove Image</div>
            </div>

            <button type="submit" name="submit" class="btn">Add Blog Post</button>
            <a href="index.php" class="btn secondary" style="margin-left: 8px;">Cancel</a>
        </form>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Image Preview JavaScript -->
    <script>
    // Image preview functionality
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove image function
    function removeImage() {
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('previewImg').src = '';
    }
    </script>

</body>
</html>