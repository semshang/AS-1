<?php
// Bootstrap file for shared application setup.
// This file is included by pages that need the database connection and initialization.

require_once __DIR__ . '/db.php';

// Helper: is the current visitor logged in?
function isLoggedIn() {
    return isset($_SESSION['userId']);
}

// Helper: is the current visitor an admin?
function isAdmin() {
    return isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1;
}

// Helper: escape output to prevent XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Helper: format a remaining-time countdown string
function timeRemaining($endDate) {
    $end  = new DateTime($endDate);
    $now  = new DateTime();
    if ($end <= $now) {
        return 'Auction ended';
    }
    $diff = $now->diff($end);
    $parts = [];
    if ($diff->d > 0) $parts[] = $diff->d . ' day' . ($diff->d != 1 ? 's' : '');
    if ($diff->h > 0) $parts[] = $diff->h . ' hour' . ($diff->h != 1 ? 's' : '');
    if ($diff->i > 0) $parts[] = $diff->i . ' minute' . ($diff->i != 1 ? 's' : '');
    return implode(' ', $parts) ?: 'Less than a minute';
}
