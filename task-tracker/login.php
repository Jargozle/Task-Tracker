<?php
// login.php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Incorrect email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Enki</title>
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
        <h2>Welcome back</h2>
        <p class="sub">Sign in to your account to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Account created! Please log in.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@email.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">Sign In</button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:.9rem;">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</div>
</body>
</html>
