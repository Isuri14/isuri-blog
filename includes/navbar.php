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

            <?php 
            if (function_exists('is_logged_in') && is_logged_in()):
                $username = function_exists('current_username') ? current_username() : 'User';
            ?>
                <a href="dashboard.php">My Blogs</a>
                <a href="create_post.php" class="btn-create">Create Post</a>
                <span class="nav-user">👤 <?= htmlspecialchars($username) ?></span>
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
    top: 0; left: 0; right: 0;
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
    flex-wrap: wrap;
}

.nav-logo {
    font-size: 1.7em;
    font-weight: bold;
    text-decoration: none;
    color: #fff;
    transition: transform 0.2s;
}
.nav-logo:hover { transform: scale(1.05); }

/* Nav links */
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
    transition: background 0.2s;
    white-space: nowrap;
}
.nav-links a:hover { background: rgba(255,255,255,0.2); }

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

/* Mobile toggle */
.nav-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.7em;
    color: #fff;
    cursor: pointer;
}

/* Search form - equal widths */
.nav-search {
    display: flex;
    height: 36px;
}

.nav-search input,
.nav-search button {
    width: 150px; /* equal widths */
}

.nav-search input {
    padding: 8px 12px;
    border-radius: 6px 0 0 6px;
    border: none;
    outline: none;
    font-size: 0.9em;
    height: 100%;
}

.nav-search button {
    padding: 8px 14px;
    border-radius: 0 6px 6px 0;
    border: none;
    background: #3b82f6;
    color: #fff;
    cursor: pointer;
    font-size: 1.1em;
    transition: background 0.2s;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.nav-search button:hover { background: #2563eb; }

/* Mobile styling */
@media (max-width: 900px) {

    .nav-toggle { display: block; }

    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 60px;
        left: 0; right: 0;
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
    }

    .nav-search input {
        width: 100%;
        flex: 1;
    }

    .nav-search button {
        width: 60px;
    }
}
</style>

<script>
document.getElementById('navToggle')?.addEventListener('click', () => {
    document.getElementById('navMenu').classList.toggle('active');
});
</script>

    
