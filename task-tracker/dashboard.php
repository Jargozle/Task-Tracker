<?php
// dashboard.php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
$user = getCurrentUser($conn);
$uid  = $_SESSION['user_id'];

// Stats
$stats = [];
$rows = $conn->query("SELECT status, COUNT(*) as cnt FROM tasks WHERE user_id = $uid GROUP BY status");
$stats['total'] = 0;
$map = ['pending' => 0, 'in_progress' => 0, 'completed' => 0];
while ($r = $rows->fetch_assoc()) { $map[$r['status']] = (int)$r['cnt']; }
$stats['total']       = array_sum($map);
$stats['pending']     = $map['pending'];
$stats['in_progress'] = $map['in_progress'];
$stats['completed']   = $map['completed'];

// Overdue count
$today = date('Y-m-d');
$ov = $conn->query("SELECT COUNT(*) as cnt FROM tasks WHERE user_id=$uid AND status != 'completed' AND due_date < '$today' AND due_date IS NOT NULL");
$stats['overdue'] = $ov->fetch_assoc()['cnt'];

// Recent 5 tasks
$recent = $conn->query("SELECT * FROM tasks WHERE user_id = $uid ORDER BY created_at DESC LIMIT 5");

// Upcoming (due in next 7 days, not completed)
$week = date('Y-m-d', strtotime('+7 days'));
$upcoming = $conn->query("SELECT * FROM tasks WHERE user_id=$uid AND status != 'completed' AND due_date BETWEEN '$today' AND '$week' ORDER BY due_date ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Enki</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($user['fullname']) ?>! Here's your overview.</p>
            </div>
            <a href="tasks.php?action=add" class="btn btn-primary">+ Add Task</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Tasks</div>
                <div class="stat-value"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">In Progress</div>
                <div class="stat-value"><?= $stats['in_progress'] ?></div>
            </div>
            <div class="stat-card warn">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?= $stats['pending'] ?></div>
            </div>
            <div class="stat-card accent">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?= $stats['completed'] ?></div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Overdue</div>
                <div class="stat-value"><?= $stats['overdue'] ?></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;flex-wrap:wrap;" class="dash-grid">
            <!-- Recent Tasks -->
            <div>
                <div class="section-header">
                    <h2>Recent Tasks</h2>
                    <a href="tasks.php" class="btn btn-secondary btn-sm">View All →</a>
                </div>
                <div class="table-wrap">
                    <?php if ($recent->num_rows === 0): ?>
                        <div class="empty-state" style="padding:2rem;">
                            <div class="empty-icon">📋</div>
                            <h3>No tasks yet</h3>
                            <p><a href="tasks.php?action=add">Add your first task</a></p>
                        </div>
                    <?php else: ?>
                    <table>
                        <thead><tr>
                            <th>Task</th><th>Priority</th><th>Status</th>
                        </tr></thead>
                        <tbody>
                        <?php while ($t = $recent->fetch_assoc()): ?>
                            <tr>
                                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($t['title']) ?>
                                </td>
                                <td><span class="badge badge-<?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></span></td>
                                <td><span class="badge badge-<?= $t['status'] ?>"><?= ucwords(str_replace('_',' ',$t['status'])) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Due -->
            <div>
                <div class="section-header">
                    <h2>Due This Week</h2>
                </div>
                <div class="table-wrap">
                    <?php if ($upcoming->num_rows === 0): ?>
                        <div class="empty-state" style="padding:2rem;">
                            <div class="empty-icon">🎉</div>
                            <h3>Nothing due soon!</h3>
                            <p style="font-size:.85rem;">You're all clear for the next 7 days.</p>
                        </div>
                    <?php else: ?>
                    <table>
                        <thead><tr>
                            <th>Task</th><th>Due Date</th><th>Priority</th>
                        </tr></thead>
                        <tbody>
                        <?php while ($t = $upcoming->fetch_assoc()):
                            $isOverdue = $t['due_date'] < $today; ?>
                            <tr>
                                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <?= htmlspecialchars($t['title']) ?>
                                </td>
                                <td class="<?= $isOverdue ? 'overdue' : '' ?>">
                                    <?= $t['due_date'] ? date('M j', strtotime($t['due_date'])) : '—' ?>
                                    <?= $isOverdue ? ' ⚠' : '' ?>
                                </td>
                                <td><span class="badge badge-<?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<style>
@media(max-width:768px){.dash-grid{grid-template-columns:1fr!important}}
</style>
</body>
</html>
