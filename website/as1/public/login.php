<?php
// Process the form and redirect BEFORE including header.php
// (once header.php runs, HTML is sent and header() calls will fail)
require_once __DIR__ . '/init.php';
session_start();

// Already logged in — redirect straight away
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = getDB()->prepare("SELECT id, name, password, isAdmin FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Valid credentials — set session and redirect before any output
            $_SESSION['userId']   = $user['id'];
            $_SESSION['userName'] = $user['name'];
            $_SESSION['isAdmin']  = $user['isAdmin'];

            header('Location: /');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// No redirect needed — fall through and render the page
$pageTitle = 'Log In';
require_once __DIR__ . '/header.php';
?>

<main>
    <h1>Log In</h1>

    <?php if ($error): ?>
        <p class="flash-error"><?= e($error) ?></p>
    <?php endif; ?>

    <form action="/login.php" method="POST">
        <label>Email Address</label>
        <input type="text" name="email" value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" />

        <label>Password</label>
        <input type="password" name="password" />

        <input type="submit" name="submit" value="Log In" />
    </form>

    <p style="margin-top:1em;">Don't have an account? <a href="/register.php">Register here</a></p>

<?php require_once __DIR__ . '/footer.php'; ?>
