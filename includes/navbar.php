<?php
/**
 * includes/navbar.php
 * Modern responsive navbar for BlogWithMe
 */
?>

<nav class="navbar">
  <div class="nav-container">
    <a href="index.php" class="nav-logo">📝 BlogWithMe</a>
    <button class="nav-toggle" id="navToggle">☰</button>
    <div class="nav-links" id="navMenu">
      <a href="index.php">Home</a>

      <?php if (function_exists('is_logged_in') && is_logged_in()):
          $username = function_exists('current_username') ? current_username() : 'User';
      ?>
        <a href="dashboard.php">My Blogs</a>
        <a href="create_post.php" class="btn-create">Create Post</a>
        <span class="nav-user">👤 <?= htmlspecialchars($username); ?></span>
        <a href="logout.php" class="btn-logout">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn-login">Login</a>
        <a href="register.php" class="btn-register">Register</a>
      <?php endif; ?>

      <!-- Search Form -->
      <form action="search.php" method="GET" class="nav-search">
        <input type="text" name="q" placeholder="Search blog posts..." required>
        <button type="submit">🔍</button>
      </form>
    </div>
  </div>
</nav>

<style>
/* Navbar styling */
.navbar {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    padding: 12px 0;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.nav-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.nav-logo {
    font-size: 1.7em;
    font-weight: bold;
    text-decoration: none;
    color: #fff;
    transition: transform 0.2s;
}
.nav-logo:hover { transform: scale(1.05); }
.nav-links {
    display: flex;
    gap: 15px;
    align-items: center;
}
.nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
}
.nav-links a:hover { background: rgba(255,255,255,0.2); }
.nav-user { font-weight: 500; opacity: 0.9; }
.btn-create { background: #facc15; color: #111; }
.btn-create:hover { background: #eab308; }
.btn-logout { background: #ef4444; color: #fff; }
.btn-logout:hover { background: #dc2626; }
.btn-login, .btn-register { background: #3b82f6; }
.btn-login:hover, .btn-register:hover { background: #2563eb; }
.nav-toggle { display: none; background: none; border: none; font-size: 1.7em; color: #fff; cursor: pointer; }

/* Search form styling */
.nav-search {
    display: flex;
    align-items: center;
    margin-left: 15px;
}
.nav-search input {
    padding: 6px 10px;
    border-radius: 6px 0 0 6px;
    border: none;
    outline: none;
}
.nav-search button {
    padding: 6px 10px;
    border-radius: 0 6px 6px 0;
    border: none;
    background: #3b82f6;
    color: #fff;
    cursor: pointer;
}
.nav-search button:hover { background: #2563eb; }

/* Mobile styling */
@media (max-width: 768px) {
    .nav-toggle { display: block; }
    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 60px;
        left: 0;
        right: 0;
        background: #667eea;
        padding: 20px;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .nav-links.active { display: flex; }

    .nav-search { margin-left: 0; flex-direction: column; gap: 8px; }
    .nav-search input { border-radius: 6px; width: 100%; }
    .nav-search button { border-radius: 6px; width: 100%; }
}
</style>

<script>
document.getElementById('navToggle')?.addEventListener('click', function() {
    document.getElementById('navMenu').classList.toggle('active');
});
</script>
