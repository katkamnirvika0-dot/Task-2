<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "blog");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Search and Pagination Logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 5; // Posts per page
$offset = ($page - 1) * $limit;
$user_id = $_SESSION['user_id'];

// Build WHERE clause
$where = "user_id = $user_id";
if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $where = "user_id = $user_id AND (title LIKE '%$search_clean%' OR content LIKE '%$search_clean%')";
}

// Get total posts count
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE $where";
$count_result = mysqli_query($conn, $count_sql);
$total_posts = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_posts / $limit);

// Get posts for current page
$sql = "SELECT * FROM posts WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            background-color: #007bff;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .search-box {
            max-width: 400px;
            margin-bottom: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .post-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation (अगर आपके पास है तो) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Blog Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="create-post.php">Create Post</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="dashboard-header text-center">
            <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
            <p>Manage your blog posts here</p>
        </div>
        
        <!-- Search Form -->
        <div class="search-box">
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search your posts..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <?php if(!empty($search)): ?>
                        <a href="dashboard.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Total Posts</h5>
                    <h3><?php echo $total_posts; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Current Page</h5>
                    <h3><?php echo $page; ?> of <?php echo $total_pages; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Actions</h5>
                    <a href="create-post.php" class="btn btn-success">Create New Post</a>
                </div>
            </div>
        </div>
        
        <!-- Posts Table -->
        <div class="post-table">
            <h3 class="mb-3">Your Posts</h3>
            
            <?php if(!empty($search)): ?>
                <div class="alert alert-info mb-3">
                    Found <?php echo $total_posts; ?> post(s) matching "<?php echo htmlspecialchars($search); ?>"
                </div>
            <?php endif; ?>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($post = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td>
                                    <span class="badge <?php echo $post['published'] ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo $post['published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <a href="edit-post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="delete-post.php?id=<?php echo $post['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" 
                                       href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                       href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-4">
                    <?php if(!empty($search)): ?>
                        <p>No posts found matching your search.</p>
                    <?php else: ?>
                        <p>You have no posts yet.</p>
                    <?php endif; ?>
                    <a href="create-post.php" class="btn btn-primary">Create Your First Post</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>