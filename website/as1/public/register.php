<?php
require_once __DIR__ . '/init.php';
session_start();

if (isLoggedIn()) {
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
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = getDB()->prepare("SELECT id FROM user WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = getDB()->prepare("INSERT INTO user (name, email, password) VALUES (?, ?, ?)");
            $ins->execute([$name, $email, $hashed]);

            $_SESSION['userId']   = getDB()->lastInsertId();
            $_SESSION['userName'] = $name;
            $_SESSION['isAdmin']  = 0;

            header('Location: /');
            exit;
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Create an Account</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/register.php" method="POST">
        <label>Display Name</label>
        <input type="text" name="name" value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>" />

        <label>Email Address</label>
        <input type="text" name="email" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" />

        <label>Password</label>
        <input type="password" name="password" />

        <input type="submit" value="Register" />
    </form>

    <p style="margin-top:1em;">Already have an account? <a href="/login.php">Log in here</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
