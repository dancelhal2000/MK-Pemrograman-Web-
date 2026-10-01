<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/remember.php';
clear_remember_cookie();
session_destroy();
header('Location: login.php');
exit;
