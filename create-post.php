<?php
// Start session and database connection
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'blog';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars(strip_tags(trim($_POST['title'])));
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($content)) {
        $error = "Title and content are required!";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $title, $content, $user_id);
        
        if ($stmt->execute()) {
            $success = "Post created successfully!";
            // Clear form
            $title = $content = '';
        } else {
            $error = "Failed to create post!";
        }
        
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Blog</title>
    <link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Create New Post</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?php echo $title ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Content:</label>
                <textarea name="content" rows="10" required><?php echo $content ?? ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Create Post</button>
                <a href="dashboard.php" class="btn cancel">Cancel</a>
            </div>
        </form>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>