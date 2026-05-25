<?php
require_once __DIR__ . '/init.php';
session_start();

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$db      = getDB();
$adminId = (int) ($_GET['id'] ?? 0);
$error   = '';

$stmt = $db->prepare("SELECT id, name, email FROM user WHERE id = ? AND isAdmin = 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: /manageAdmins.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->prepare("UPDATE user SET name = ?, email = ?, password = ? WHERE id = ?")
               ->execute([$name, $email, $hash, $adminId]);
        } else {
            $db->prepare("UPDATE user SET name = ?, email = ? WHERE id = ?")
               ->execute([$name, $email, $adminId]);
        }
        header('Location: /manageAdmins.php');
        exit;
    }

    $admin['name']  = $name;
    $admin['email'] = $email;
}

$pageTitle = 'Edit Admin';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Edit Admin Account</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/editAdmin.php?id=<?= $adminId ?>" method="POST">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= e($admin['name']) ?>" />

        <label>Email Address</label>
        <input type="text" name="email" value="<?= e($admin['email']) ?>" />

        <label>New Password (leave blank to keep current)</label>
        <input type="password" name="password" />

        <input type="submit" value="Save Changes" />
    </form>

    <p style="margin-top:1em;"><a href="/manageAdmins.php">&larr; Back to admin list</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
