<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "blog");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Search and Pagination Logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 6; // Posts per page
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = "p.published = TRUE";
if (!empty($search)) {
    $search_clean = mysqli_real_escape_string($conn, $search);
    $where = "p.published = TRUE AND (p.title LIKE '%$search_clean%' OR p.content LIKE '%$search_clean%')";
}

// Get total posts count
$count_sql = "SELECT COUNT(*) as total FROM posts p WHERE $where";
$count_result = mysqli_query($conn, $count_sql);
$total_posts = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_posts / $limit);

// Get posts for current page with author info
$sql = "SELECT p.*, u.username, u.email 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE $where 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Get popular posts for sidebar
$popular_sql = "SELECT p.*, u.username, 
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
               FROM posts p 
               LEFT JOIN users u ON p.user_id = u.id 
               WHERE p.published = TRUE 
               ORDER BY p.created_at DESC 
               LIMIT 5";
$popular_result = mysqli_query($conn, $popular_sql);

// Get categories/tags (simulating from posts)
$tags_sql = "SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(p.title, ' ', 1), ' ', 1) as tag 
            FROM posts p 
            WHERE p.published = TRUE 
            LIMIT 10";
$tags_result = mysqli_query($conn, $tags_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlogHub - Share Your Thoughts</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4895ef;
            --secondary: #f72585;
            --accent: #4cc9f0;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --border: #e2e8f0;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            background: var(--light);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header & Navigation */
        header {
            background: white;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo i {
            font-size: 32px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }
        
        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--radius);
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: var(--light);
            color: var(--primary);
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 24px;
            border-radius: var(--radius);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100"><path fill="white" opacity="0.1" d="M0,0V100H1000V0C800,50,200,50,0,0Z"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            margin-bottom: 20px;
            color: white;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        
        /* Search Section */
        .search-section {
            background: white;
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: -80px auto 60px;
            position: relative;
            z-index: 2;
        }
        
        .search-box {
            display: flex;
            gap: 15px;
        }
        
        .search-input {
            flex: 1;
            padding: 18px 24px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        /* Posts Grid */
        .posts-grid {
            display: grid;
            gap: 30px;
        }
        
        .post-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        
        .post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .post-image {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            position: relative;
        }
        
        .post-category {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .post-content {
            padding: 30px;
        }
        
        .post-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .post-author {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .post-author-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .post-title {
            font-size: 22px;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .post-title a {
            color: var(--dark);
            text-decoration: none;
        }
        
        .post-title a:hover {
            color: var(--primary);
        }
        
        .post-excerpt {
            color: var(--gray);
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .read-time {
            color: var(--gray);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Sidebar */
        .sidebar-widget {
            background: white;
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        
        .widget-title {
            font-size: 18px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .popular-posts {
            display: grid;
            gap: 20px;
        }
        
        .popular-post {
            display: flex;
            gap: 15px;
            padding: 15px;
            border-radius: var(--radius);
            transition: all 0.3s;
        }
        
        .popular-post:hover {
            background: var(--light);
        }
        
        .popular-post-image {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            flex-shrink: 0;
        }
        
        .popular-post-content h4 {
            font-size: 15px;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .popular-post-content p {
            font-size: 12px;
            color: var(--gray);
        }
        
        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .tag {
            padding: 8px 16px;
            background: var(--light);
            color: var(--dark);
            border-radius: 20px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .tag:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 50px 0;
        }
        
        .page-link {
            padding: 12px 20px;
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .page-link:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .page-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-color: transparent;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: var(--border);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto 30px;
        }
        
        /* Call to Action */
        .cta-section {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 80px 0;
            text-align: center;
            border-radius: var(--radius);
            margin: 60px 0;
        }
        
        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .cta-content h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: white;
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #334155;
            color: #94a3b8;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .search-section {
                padding: 30px;
                margin: -60px auto 40px;
            }
            
            .search-box {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-blog"></i> BlogHub
                </a>
                
                <ul class="nav-links">
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="#">Categories</a></li>
                    <li><a href="#">Trending</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
                
                <div class="nav-buttons">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to BlogHub</h1>
                <p>Discover amazing articles, share your thoughts, and connect with writers from around the world.</p>
                <a href="#posts" class="btn btn-primary" style="background: white; color: var(--primary);">
                    <i class="fas fa-book-reader"></i> Start Reading
                </a>
            </div>
        </div>
    </section>
    
    <!-- Search Section -->
    <div class="container">
        <div class="search-section">
            <form method="GET" action="">
                <div class="search-box">
                    <input type="text" 
                           class="search-input" 
                           name="search" 
                           placeholder="Search posts by title or content..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if(!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <!-- Posts Section -->
            <main>
                <div id="posts">
                    <?php if(!empty($search)): ?>
                        <div style="background: #e0f2fe; color: #0369a1; padding: 15px; border-radius: var(--radius); margin-bottom: 30px;">
                            <i class="fas fa-search"></i> 
                            Found <?php echo $total_posts; ?> result(s) for "<?php echo htmlspecialchars($search); ?>"
                        </div>
                    <?php endif; ?>
                    
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <div class="posts-grid">
                            <?php while($post = mysqli_fetch_assoc($result)): 
                                $initial = strtoupper(substr($post['username'], 0, 1));
                                $read_time = ceil(str_word_count(strip_tags($post['content'])) / 200);
                            ?>
                                <article class="post-card">
                                    <div class="post-image">
                                        <div class="post-category">Blog Post</div>
                                    </div>
                                    <div class="post-content">
                                        <div class="post-meta">
                                            <div class="post-author">
                                                <div class="post-author-avatar"><?php echo $initial; ?></div>
                                                <span><?php echo htmlspecialchars($post['username']); ?></span>
                                            </div>
                                            <div>
                                                <i class="far fa-calendar"></i>
                                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                            </div>
                                        </div>
                                        
                                        <h2 class="post-title">
                                            <a href="post.php?id=<?php echo $post['id']; ?>">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h2>
                                        
                                        <p class="post-excerpt">
                                            <?php 
                                            $content = strip_tags($post['content']);
                                            echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                                            ?>
                                        </p>
                                        
                                        <div class="post-footer">
                                            <div class="read-time">
                                                <i class="far fa-clock"></i>
                                                <?php echo $read_time; ?> min read
                                            </div>
                                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline btn-sm">
                                                Read More <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                       class="page-link">
                                        <i class="fas fa-chevron-left"></i> Prev
                                    </a>
                                <?php endif; ?>
                                
                                <?php 
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for($i = $start; $i <= $end; $i++): 
                                ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                       class="page-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-newspaper"></i>
                            <h3>No Posts Found</h3>
                            <p>
                                <?php if(!empty($search)): ?>
                                    No posts match your search criteria. Try different keywords or browse all posts.
                                <?php else: ?>
                                    There are no published posts yet. Check back soon or 
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="create-post.php">create your own post</a>.
                                    <?php else: ?>
                                        <a href="register.php">sign up</a> to be the first to post.
                                    <?php endif; ?>
                                </p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home"></i> Back to Home
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
            
            <!-- Sidebar -->
            <aside>
                <!-- Popular Posts -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-fire"></i> Popular Posts
                    </h3>
                    <div class="popular-posts">
                        <?php if(mysqli_num_rows($popular_result) > 0): ?>
                            <?php while($popular = mysqli_fetch_assoc($popular_result)): ?>
                                <a href="post.php?id=<?php echo $popular['id']; ?>" class="popular-post">
                                    <div class="popular-post-image"></div>
                                    <div class="popular-post-content">
                                        <h4><?php echo htmlspecialchars($popular['title']); ?></h4>
                                        <p>By <?php echo htmlspecialchars($popular['username']); ?></p>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--gray);">No popular posts yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        <i class="fas fa-tags"></i> Explore Tags
                    </h3>
                    <div class="tags">
                        <?php if(mysqli_num_rows($tags_result) > 0): ?>
                            <?php while($tag = mysqli_fetch_assoc($tags_result)): ?>
                                <a href="?search=<?php echo urlencode($tag['tag']); ?>" class="tag">
                                    <?php echo htmlspecialchars($tag['tag']); ?>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--gray);">No tags available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- CTA for Writers -->
                <div class="sidebar-widget" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white;">
                    <h3 class="widget-title" style="color: white; border-color: rgba(255,255,255,0.2);">
                        <i class="fas fa-pen-fancy"></i> Start Writing
                    </h3>
                    <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px;">
                        Share your thoughts with our community of readers.
                    </p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="create-post.php" class="btn" style="background: white; color: var(--primary); width: 100%; text-align: center;">
                            <i class="fas fa-plus"></i> Create Post
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn" style="background: white; color: var(--primary); width: 100%; text-align: center;">
                            <i class="fas fa-user-plus"></i> Join Now
                        </a>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="container">
        <section class="cta-section">
            <div class="cta-content">
                <h2>Ready to Start Writing?</h2>
                <p style="color: rgba(255,255,255,0.9); margin-bottom: 30px;">
                    Join thousands of writers sharing their stories and knowledge.
                </p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="create-post.php" class="btn" style="background: white; color: var(--primary);">
                        <i class="fas fa-pen"></i> Write Your First Post
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn" style="background: white; color: var(--primary);">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>BlogHub</h4>
                    <p style="color: #94a3b8;">
                        A platform for writers and readers to connect, share, and grow together.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Categories</h4>
                    <ul class="footer-links">
                        <li><a href="#">Technology</a></li>
                        <li><a href="#">Lifestyle</a></li>
                        <li><a href="#">Education</a></li>
                        <li><a href="#">Business</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Connect</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BlogHub. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Simple animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate post cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            document.querySelectorAll('.post-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s, transform 0.5s';
                observer.observe(card);
            });
            
            // Search input focus effect
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.parentElement.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.15)';
                });
                
                searchInput.addEventListener('blur', function() {
                    this.parentElement.style.boxShadow = '';
                });
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>