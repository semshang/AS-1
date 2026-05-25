<?php
// Shared header — included at the top of every page
// Starts the session and outputs the consistent <header> + <nav>

require_once __DIR__ . '/init.php';

// Only start a session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch all categories for the navigation bar
$categories = getDB()->query("SELECT id, name FROM category ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — Carbuy' : 'Carbuy Auctions' ?></title>
    <link rel="stylesheet" href="/carbuy.css" />
    <style>
        /* Small additions on top of the supplied stylesheet */
        .flash-error   { background: #fdd; border: 1px solid #f99; padding: 0.8em 1em; margin-bottom: 1em; }
        .flash-success { background: #dfd; border: 1px solid #9d9; padding: 0.8em 1em; margin-bottom: 1em; }
        .auth-links    { text-align: right; padding: 0.3em 1em; font-size: 0.95em; border-bottom: 1px solid #ddd; }
        .auth-links a  { margin-left: 1em; }
        .admin-bar     { background: #222; color: #eee; padding: 0.4em 1em; font-size: 0.9em; }
        .admin-bar a   { color: #adf; margin-right: 1em; }
        nav ul li a.active { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>

<!-- Auth / admin quick-links bar -->
<div class="auth-links">
    <?php if (isLoggedIn()): ?>
        Logged in as <strong><?= e($_SESSION['userName']) ?></strong>
        <?php if (isAdmin()): ?>
            &nbsp;|&nbsp; <a href="/adminCategories.php">Admin Panel</a>
        <?php endif; ?>
        &nbsp;|&nbsp; <a href="/logout.php">Log out</a>
    <?php else: ?>
        <a href="/login.php">Log in</a>
        <a href="/register.php">Register</a>
    <?php endif; ?>
</div>

<header>
    <h1>
        <a href="/" style="text-decoration:none;color:inherit;">
            <span class="C">C</span><span class="a">a</span><span class="r">r</span><span class="b">b</span><span class="u">u</span><span class="y">y</span>
        </a>
    </h1>
    <form action="/search.php" method="GET">
        <input type="text" name="q" placeholder="Search for a car"
               value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" />
        <input type="submit" value="Search" />
    </form>
</header>

<nav>
    <ul>
        <?php foreach ($categories as $cat): ?>
            <li>
                <a class="categoryLink" href="/category.php?id=<?= $cat['id'] ?>">
                    <?= e($cat['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        <?php if (isLoggedIn()): ?>
            <li><a href="/addAuction.php">+ List a Car</a></li>
        <?php endif; ?>
    </ul>
</nav>

<img src="/banners/1.jpg" alt="Carbuy banner" />
