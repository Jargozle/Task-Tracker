<?php
// includes/auth.php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: dashboard.php");
        exit();
    }
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    $id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, fullname, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
