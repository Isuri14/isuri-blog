<?php
/**
 * includes/navbar.php
 * Modern responsive navbar for BlogWithMe
 */
?>
<nav class="navbar" role="navigation">
  <div class="nav-container">

    <!-- Logo -->
    <a href="index.php" class="nav-logo" aria-label="BlogWithMe Home">📝 BlogWithMe</a>

    <!-- Mobile Toggle -->
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation menu" aria-expanded="false">
      ☰
    </button>

    <!-- Nav Menu -->
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
      <form action="search.php" method="GET" class="nav-search" role="search">
        <input type="text" name="q" placeholder="Search..." aria-label="Search posts" required>
        <button type="submit" aria-label="Search">🔍</button>
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
    max-width: 1200px;
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

/* Links */
.nav-links {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.nav-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
    white-space: nowrap;
}
.nav-links a:hover { background: rgba(255,255,255,0.2); }

/* User text */
.nav-user {
    font-weight: 500;
    opacity: 0.9;
    padding: 6px 12px;
    white-space: nowrap;
}

/* Buttons */
.btn-create { background: #facc15; color: #111; }
.btn-create:hover { background: #eab308; }

.btn-logout { background: #ef4444; color: #fff; }
.btn-logout:hover { background: #dc2626; }

.btn-login, .btn-register { background: #3b82f6; }
.btn-login:hover, .btn-register:hover { background: #2563eb; }

/* Mobile Toggle */
.nav-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.7em;
    color: #fff;
    cursor: pointer;
}

/* Search Bar */
.nav-search {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: 40px;
    min-width: 280px;
}
.nav-search input {
    flex: 1;
    padding: 10px 20px;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.95em;
    color: #333;
}
.nav-search input::placeholder { color: #999; }

.nav-search button {
    width: 45px;
    height: 40px;
    border: none;
    background: #00bcd4;
    color: #fff;
    cursor: pointer;
    font-size: 1.2em;
    transition: background 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nav-search button:hover { background: #00acc1; }

/* Mobile */
@media (max-width: 900px) {
    .nav-toggle { display: block; }

    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 60px;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 20px;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    .nav-links.active { display: flex; }

    .nav-links a,
    .nav-user {
        width: 100%;
        text-align: center;
    }
    .nav-search {
        width: 100%;
        margin-top: 8px;
        min-width: auto;
    }
}
</style>

<script>
const toggle = document.getElementById('navToggle');
const menu = document.getElementById('navMenu');

toggle?.addEventListener('click', () => {
    menu.classList.toggle('active');
    const expanded = toggle.getAttribute("aria-expanded") === "true";
    toggle.setAttribute("aria-expanded", !expanded);
});
</script>
