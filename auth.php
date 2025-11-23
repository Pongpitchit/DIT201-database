<?php
require_once __DIR__ . '/config.php';

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function current_username() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}
