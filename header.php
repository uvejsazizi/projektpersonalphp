<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$navItems = [
    'index.php' => 'Home',
    'register.php' => 'Register',
    'login.php' => 'Login',
];
?>
<header class="site-navbar">
    <div class="navbar-brand">
        <a class="logo" href="index.php">MySystem</a>
        <span class="brand-tag">Personal</span>
    </div>

    <nav class="site-nav" aria-label="Primary navigation">
        <?php foreach ($navItems as $href => $label): ?>
            <a href="<?= $href ?>" class="nav-link<?= $currentPage === $href ? ' active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

    <a class="navbar-cta" href="register.php">Start free</a>
</header>

<style>
.site-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 22px;
    padding: 18px 24px;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 22px 55px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(20px);
    margin-bottom: 28px;
}
.navbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.logo {
    font-size: 1.4rem;
    font-weight: 800;
    color: #2563eb;
    text-decoration: none;
}
.brand-tag {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(59, 132, 246, 0.12);
    color: #1d4ed8;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.site-nav {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 22px;
}
.nav-link {
    color: #4b5563;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s ease, transform 0.2s ease;
}
.nav-link:hover,
.nav-link.active {
    color: #1d4ed8;
    transform: translateY(-1px);
}
.navbar-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 24px;
    border-radius: 999px;
    background: #2563eb;
    color: white;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 16px 28px rgba(59, 132, 246, 0.22);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.navbar-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 34px rgba(59, 132, 246, 0.28);
}
@media (max-width: 780px) {
    .site-navbar {
        flex-direction: column;
        align-items: stretch;
    }
    .site-nav {
        justify-content: space-between;
        gap: 16px;
        width: 100%;
    }
    .navbar-cta {
        width: 100%;
    }
}
</style>
