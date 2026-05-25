<?php
require_once __DIR__ . '/connection.php';
session_start();

function isAdmin() {
    return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1;
}

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$adminId = (int) ($_GET['id'] ?? 0);

// Don't allow deleting yourself
if ($adminId && $adminId != $_SESSION['userId']) {
    getDB()->prepare("DELETE FROM user WHERE id = ? AND isAdmin = 1")->execute([$adminId]);
}

header('Location: /manageAdmins.php');
exit;
