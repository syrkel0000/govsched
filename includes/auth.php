<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isApplicant() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'applicant';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /govsched/index.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /govsched/index.php');
        exit();
    }
}

function requireApplicant() {
    if (!isApplicant()) {
        header('Location: /govsched/index.php');
        exit();
    }
}
?>