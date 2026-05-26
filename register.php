<?php
// register.php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with that email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
            $stmt2->bind_param("sss", $fullname, $email, $hashed);
            if ($stmt2->execute()) {
                $success = 'Account created! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Enki</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <svg class="logo-icon" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="2" width="32" height="32" rx="7" fill="#5c3310"/>
                <rect x="6" y="6" width="24" height="24" rx="4" fill="#ffffff"/>
                <rect x="14" y="10" width="10" height="2.2" rx="1.1" fill="#5c3310"/>
                <rect x="14" y="16.9" width="10" height="2.2" rx="1.1" fill="#5c3310"/>
                <rect x="14" y="23.8" width="7"  height="2.2" rx="1.1" fill="#5c3310"/>
                <polyline points="9.5,11.8 11.2,13.5 13.5,10.5" fill="none" stroke="#5c3310" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="9.5,18.7 11.2,20.4 13.5,17.4" fill="none" stroke="#5c3310" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="9" y="23.5" width="3.2" height="3.2" rx=".8" fill="none" stroke="#5c3310" stroke-width="1.5"/>
            </svg>
            <span>Enki</span>
        </div>
        <h2>Create account</h2>
        <p class="sub">Start tracking your tasks today</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="login.php">Log in →</a></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="Juan dela Cruz"
                    value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm" placeholder="Repeat password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">Create Account</button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:.9rem;">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</div>
</body>
</html>
