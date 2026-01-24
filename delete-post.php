<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Direct database connection
$host = 'localhost';
$dbname = 'blog';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if(!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$post_id = $_GET['id'];

// Verify post belongs to user
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION['user_id']]);
$post = $stmt->fetch();

if(!$post) {
    header('Location: dashboard.php');
    exit;
}

// Delete post
if(isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $conn->prepare("DELETE FROM posts WHERE id = ?")->execute([$post_id]);
    header('Location: dashboard.php?deleted=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post | Professional Blog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .delete-container {
            text-align: center;
            padding: 40px 20px;
        }
        
        .warning-box {
            background: #fde8e8;
            border: 2px solid var(--accent);
            border-radius: var(--radius);
            padding: 30px;
            margin: 30px 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .delete-btn {
            background: var(--accent);
            color: white;
            min-width: 150px;
        }
        
        .cancel-btn {
            background: var(--dark-gray);
            color: white;
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <h1><i class="fas fa-trash-alt"></i> Delete Post</h1>
                <p class="tagline">Confirm deletion</p>
            </div>
            <nav class="main-nav">
                <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </nav>
        </header>

        <main class="main-content">
            <div class="form-container delete-container">
                <div class="warning-box">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: var(--accent); margin-bottom: 20px;"></i>
                    <h2 style="color: var(--accent); margin-bottom: 15px;">Delete Post</h2>
                    <h3 style="color: var(--secondary); margin-bottom: 15px;">"<?php echo htmlspecialchars($post['title']); ?>"</h3>
                    <p style="color: var(--text-light); margin-bottom: 10px;">
                        <i class="far fa-calendar"></i> Created on: <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </p>
                    <p style="color: var(--dark-gray);">
                        This action cannot be undone. All data associated with this post will be permanently deleted.
                    </p>
                </div>
                
                <div class="action-buttons">
                    <a href="delete-post.php?id=<?php echo $post_id; ?>&confirm=yes" 
                       class="btn delete-btn">
                        <i class="fas fa-trash"></i> Yes, Delete
                    </a>
                    <a href="dashboard.php" class="btn cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
                
                <div class="form-footer" style="margin-top: 30px;">
                    <a href="edit-post.php?id=<?php echo $post_id; ?>">
                        <i class="fas fa-edit"></i> Edit instead of delete
                    </a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Professional Blog. All rights reserved.</p>
            <p>Think before you delete</p>
        </footer>
    </div>
</body>
</html>