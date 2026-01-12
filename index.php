<?php
// Start session and database connection
session_start();
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'blog';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all posts with usernames
$sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome to Our Blog</h1>
            <div class="auth-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a> | 
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a> | 
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="posts-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($post = $result->fetch_assoc()): ?>
                    <article class="post">
                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                        <div class="post-meta">
                            <span>By: <?php echo htmlspecialchars($post['username']); ?></span>
                            <span>Posted on: <?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                    </article>
                    <hr>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-posts">No posts available. Be the first to create one!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
