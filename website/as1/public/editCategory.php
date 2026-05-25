<?php
require_once __DIR__ . '/init.php';
session_start();

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$db         = getDB();
$categoryId = (int) ($_GET['id'] ?? 0);
$error      = '';

$stmt = $db->prepare("SELECT id, name FROM category WHERE id = ?");
$stmt->execute([$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: /adminCategories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        $db->prepare("UPDATE category SET name = ? WHERE id = ?")->execute([$name, $categoryId]);
        header('Location: /adminCategories.php');
        exit;
    }

    $category['name'] = $name;
}

$pageTitle = 'Edit Category';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Edit Category</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/editCategory.php?id=<?= $categoryId ?>" method="POST">
        <label>Category Name</label>
        <input type="text" name="name" value="<?= e($category['name']) ?>" />
        <input type="submit" value="Save Changes" />
    </form>

    <p style="margin-top:1em;"><a href="/adminCategories.php">&larr; Back to categories</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
