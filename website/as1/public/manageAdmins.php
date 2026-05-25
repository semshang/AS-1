<?php
$pageTitle = 'Manage Admins';
require_once __DIR__ . '/header.php';

if (!isAdmin()) {
    echo '<main><p>Access denied.</p>';
    require_once __DIR__ . '/footer.php';
    exit;
}

$admins = getDB()->query("SELECT id, name, email FROM user WHERE isAdmin = 1 ORDER BY name")->fetchAll();
?>

<main>
    <h1>Manage Admin Users</h1>
    <p><a href="/addAdmin.php">+ Add New Admin</a></p>

    <table style="width:100%; border-collapse:collapse; margin-top:1em;">
        <thead>
            <tr style="border-bottom:2px solid #ddd;">
                <th style="text-align:left; padding:0.5em;">Name</th>
                <th style="text-align:left; padding:0.5em;">Email</th>
                <th style="text-align:right; padding:0.5em;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:0.6em;"><?= e($admin['name']) ?></td>
                    <td style="padding:0.6em;"><?= e($admin['email']) ?></td>
                    <td style="text-align:right; padding:0.6em;">
                        <a href="/editAdmin.php?id=<?= $admin['id'] ?>">Edit</a>
                        <?php if ($admin['id'] != $_SESSION['userId']): ?>
                            &nbsp;|&nbsp;
                            <a href="/deleteAdmin.php?id=<?= $admin['id'] ?>"
                               onclick="return confirm('Remove this admin?');"
                               style="color:#c0392b;">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:1em;"><a href="/adminCategories.php">&larr; Back to Admin Panel</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
