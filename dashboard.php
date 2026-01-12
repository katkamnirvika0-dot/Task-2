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

$user_id = $_SESSION['user_id'];

// Get all posts by the current user
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>My Blog Posts</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['username']; ?>!
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>
        
        <nav>
            <a href="create-post.php" class="btn">+ Create New Post</a>
            <a href="index.php" class="btn">View Public Posts</a>
        </nav>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="posts-grid">
                <?php while ($post = $result->fetch_assoc()): ?>
                    <div class="post-card">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="post-date">
                            <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                        </p>
                        <p class="post-content">
                            <?php 
                            $content = $post['content'];
                            echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
                            ?>
                        </p>
                        <div class="post-actions">
                            <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn edit">Edit</a>
                            <a href="delete-post.php?id=<?php echo $post['id']; ?>" 
                               class="btn delete" 
                               onclick="return confirm('Are you sure you want to delete this post?')">
                                Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No posts yet. Create your first post!</p>
                <a href="create-post.php" class="btn">Create First Post</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
