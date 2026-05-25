<?php
require_once __DIR__ . '/init.php';
session_start();

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $check = getDB()->prepare("SELECT id FROM user WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'A user with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            getDB()->prepare("INSERT INTO user (name, email, password, isAdmin) VALUES (?, ?, ?, 1)")
                   ->execute([$name, $email, $hash]);
            header('Location: /manageAdmins.php');
            exit;
        }
    }
}

$pageTitle = 'Add Admin';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Add Administrator Account</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/addAdmin.php" method="POST">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>" />

        <label>Email Address</label>
        <input type="text" name="email" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" />

        <label>Password</label>
        <input type="password" name="password" />

        <input type="submit" value="Create Admin Account" />
    </form>

    <p style="margin-top:1em;"><a href="/manageAdmins.php">&larr; Back to admin list</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
