<?php
// includes/sidebar.php
$current = basename($_SERVER['PHP_SELF']);
$user    = $user ?? getCurrentUser($conn);
$initials = strtoupper(substr($user['fullname'], 0, 1));
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <!-- Checklist logo: brown outer, white inner -->
        <svg class="logo-icon" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="2" width="32" height="32" rx="7" fill="#5c3310"/>
            <rect x="6" y="6" width="24" height="24" rx="4" fill="#ffffff"/>
            <!-- Checklist lines -->
            <rect x="14" y="10" width="10" height="2.2" rx="1.1" fill="#5c3310"/>
            <rect x="14" y="16.9" width="10" height="2.2" rx="1.1" fill="#5c3310"/>
            <rect x="14" y="23.8" width="7"  height="2.2" rx="1.1" fill="#5c3310"/>
            <!-- Check marks -->
            <polyline points="9.5,11.8 11.2,13.5 13.5,10.5" fill="none" stroke="#5c3310" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="9.5,18.7 11.2,20.4 13.5,17.4" fill="none" stroke="#5c3310" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <!-- Unchecked box for last item -->
            <rect x="9" y="23.5" width="3.2" height="3.2" rx=".8" fill="none" stroke="#5c3310" stroke-width="1.5"/>
        </svg>
        <span>Enki</span>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-label">Menu</span>
        <a href="dashboard.php" class="nav-link <?= $current === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">⊞</span> Dashboard
        </a>
        <a href="tasks.php" class="nav-link <?= $current === 'tasks.php' ? 'active' : '' ?>">
            <span class="icon">✓</span> Tasks
        </a>

        <span class="nav-label">Account</span>
        <a href="logout.php" class="nav-link" onclick="return confirm('Log out?')">
            <span class="icon">⏻</span> Logout
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="avatar"><?= $initials ?></div>
            <div>
                <div class="uname"><?= htmlspecialchars($user['fullname']) ?></div>
                <div class="uemail"><?= htmlspecialchars($user['email']) ?></div>
            </div>
        </div>
    </div>
</aside>
