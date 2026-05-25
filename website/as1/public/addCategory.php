<?php
require_once __DIR__ . '/init.php';
session_start();

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        $check = getDB()->prepare("SELECT id FROM category WHERE name = ?");
        $check->execute([$name]);

        if ($check->fetch()) {
            $error = 'A category with that name already exists.';
        } else {
            getDB()->prepare("INSERT INTO category (name) VALUES (?)")->execute([$name]);
            header('Location: /adminCategories.php');
            exit;
        }
    }
}

$pageTitle = 'Add Category';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Add Category</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/addCategory.php" method="POST">
        <label>Category Name</label>
        <input type="text" name="name" value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>" />
        <input type="submit" value="Add Category" />
    </form>

    <p style="margin-top:1em;"><a href="/adminCategories.php">&larr; Back to categories</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
