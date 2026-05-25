<?php
// Deletes a category when visited (linked from adminCategories.php)
require_once __DIR__ . '/connection.php';
session_start();

// Helpers needed before header.php
function isAdmin() {
    return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1;
}

if (!isAdmin()) {
    header('Location: /');
    exit;
}

$categoryId = (int) ($_GET['id'] ?? 0);

if ($categoryId) {
    // Prevent deletion if auctions are still using this category
    $check = getDB()->prepare("SELECT COUNT(*) FROM auction WHERE categoryId = ?");
    $check->execute([$categoryId]);

    if ($check->fetchColumn() > 0) {
        // Redirect back with an error message in the session
        $_SESSION['adminError'] = 'Cannot delete a category that has active auctions assigned to it.';
    } else {
        getDB()->prepare("DELETE FROM category WHERE id = ?")->execute([$categoryId]);
    }
}

header('Location: /adminCategories.php');
exit;
