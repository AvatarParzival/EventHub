<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
}

function logout() {
    session_destroy();
    redirect('index.php');
}
?>