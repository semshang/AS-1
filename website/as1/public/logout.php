<?php
// Destroy the session and redirect to home
session_start();
session_destroy();
header('Location: /');
exit;
