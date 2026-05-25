<?php
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/header.php';

// Admin only
if (!isAdmin()) {
    echo '<main><p>Access denied. This page is for administrators only.</p>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$categories = getDB()->query("SELECT id, name FROM category ORDER BY name ASC")->fetchAll();
?>

<main>
    <h1>Manage Categories</h1>
    <p><a href="/addCategory.php">+ Add New Category</a></p>

    <?php if (empty($categories)): ?>
        <p>No categories yet. <a href="/addCategory.php">Add one!</a></p>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:1em;">
            <thead>
                <tr style="border-bottom:2px solid #ddd;">
                    <th style="text-align:left; padding:0.5em;">Category Name</th>
                    <th style="text-align:right; padding:0.5em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:0.6em;"><?= e($cat['name']) ?></td>
                        <td style="text-align:right; padding:0.6em;">
                            <a href="/editCategory.php?id=<?= $cat['id'] ?>">Edit</a>
                            &nbsp;|&nbsp;
                            <a href="/deleteCategory.php?id=<?= $cat['id'] ?>"
                               onclick="return confirm('Delete the &quot;<?= e(addslashes($cat['name'])) ?>&quot; category?');"
                               style="color:#c0392b;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top:2em;"><a href="/manageAdmins.php">Manage Admin Users</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
