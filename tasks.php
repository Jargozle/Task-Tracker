<?php
// tasks.php — Full CRUD operations
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();
$user = getCurrentUser($conn);
$uid  = $_SESSION['user_id'];
$today = date('Y-m-d');

$msg   = '';
$error = '';

// ── DELETE ───────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $del_id, $uid);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = 'Task deleted.';
    } else {
        $error = 'Could not delete task.';
    }
}

// ── MARK COMPLETE ─────────────────────────────────────────────────────────
if (isset($_GET['complete'])) {
    $cid = (int)$_GET['complete'];
    $stmt = $conn->prepare("UPDATE tasks SET status='completed' WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $cid, $uid);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = 'Task marked as complete! 🎉';
    }
}

// ── ADD ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $title    = trim($_POST['title']       ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $priority = $_POST['priority']         ?? 'medium';
    $status   = $_POST['status']           ?? 'pending';
    $due      = $_POST['due_date']         ?? null;

    if (empty($title)) {
        $error = 'Task title is required.';
    } else {
        $due = empty($due) ? null : $due;
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, priority, status, due_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $uid, $title, $desc, $priority, $status, $due);
        if ($stmt->execute()) {
            $msg = 'Task added successfully!';
        } else {
            $error = 'Failed to add task.';
        }
    }
}

// ── EDIT (load for form) ─────────────────────────────────────────────────────
$edit_task = null;
if (isset($_GET['edit'])) {
    $eid  = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $eid, $uid);
    $stmt->execute();
    $edit_task = $stmt->get_result()->fetch_assoc();
}

// ── UPDATE ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $tid      = (int)($_POST['task_id']      ?? 0);
    $title    = trim($_POST['title']         ?? '');
    $desc     = trim($_POST['description']   ?? '');
    $priority = $_POST['priority']           ?? 'medium';
    $status   = $_POST['status']             ?? 'pending';
    $due      = empty($_POST['due_date']) ? null : $_POST['due_date'];

    if (empty($title)) {
        $error = 'Task title is required.';
    } else {
        $stmt = $conn->prepare("UPDATE tasks SET title=?, description=?, priority=?, status=?, due_date=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssii", $title, $desc, $priority, $status, $due, $tid, $uid);
        if ($stmt->execute()) {
            $msg = 'Task updated successfully!';
        } else {
            $error = 'Failed to update task.';
        }
    }
}

// ── FILTERS ───────────────────────────────────────────────────────────
$filter_status   = $_GET['filter_status']   ?? '';
$filter_priority = $_GET['filter_priority'] ?? '';
$search          = trim($_GET['search']     ?? '');

$where  = "WHERE user_id = ?";
$params = [$uid];
$types  = 'i';

if ($filter_status)   { $where .= " AND status = ?";   $types .= 's'; $params[] = $filter_status;   }
if ($filter_priority) { $where .= " AND priority = ?"; $types .= 's'; $params[] = $filter_priority; }
if ($search) {
    $like = "%$search%";
    $where .= " AND (title LIKE ? OR description LIKE ?)";
    $types .= 'ss'; $params[] = $like; $params[] = $like;
}

$sql = "SELECT * FROM tasks $where ORDER BY
    FIELD(status,'in_progress','pending','completed'),
    FIELD(priority,'high','medium','low'),
    due_date ASC, created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tasks = $stmt->get_result();

// Show add form?
$show_add = isset($_GET['action']) && $_GET['action'] === 'add';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks — Enki</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <div>
                <h1>Tasks</h1>
                <p>Manage all your tasks in one place</p>
            </div>
            <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Task</button>
        </div>

        <?php if ($msg):  ?><div class="alert alert-success"><?= htmlspecialchars($msg)   ?></div><?php endif; ?>
        <?php if ($error):?><div class="alert alert-error"><?= htmlspecialchars($error)  ?></div><?php endif; ?>

        <!-- Filter Bar -->
        <form method="GET" action="tasks.php" class="filter-bar">
            <input type="text" name="search" placeholder="🔍 Search tasks…" value="<?= htmlspecialchars($search) ?>">
            <select name="filter_status">
                <option value="">All Statuses</option>
                <option value="pending"     <?= $filter_status==='pending'     ?'selected':'' ?>>Pending</option>
                <option value="in_progress" <?= $filter_status==='in_progress' ?'selected':'' ?>>In Progress</option>
                <option value="completed"   <?= $filter_status==='completed'   ?'selected':'' ?>>Completed</option>
            </select>
            <select name="filter_priority">
                <option value="">All Priorities</option>
                <option value="high"   <?= $filter_priority==='high'  ?'selected':'' ?>>High</option>
                <option value="medium" <?= $filter_priority==='medium'?'selected':'' ?>>Medium</option>
                <option value="low"    <?= $filter_priority==='low'   ?'selected':'' ?>>Low</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="tasks.php" class="btn btn-secondary">Clear</a>
        </form>

        <!-- Tasks Table -->
        <div class="table-wrap">
            <?php if ($tasks->num_rows === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>No tasks found</h3>
                    <p>Try adjusting your filters or <a href="#" onclick="openModal('addModal')">add a new task</a>.</p>
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i=1; while ($t = $tasks->fetch_assoc()):
                    $isOverdue = $t['due_date'] && $t['due_date'] < $today && $t['status'] !== 'completed';
                ?>
                    <tr>
                        <td style="color:var(--text-muted)"><?= $i++ ?></td>
                        <td style="max-width:180px;">
                            <span style="<?= $t['status']==='completed' ? 'text-decoration:line-through;opacity:.5;' : '' ?>">
                                <?= htmlspecialchars($t['title']) ?>
                            </span>
                        </td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-muted);font-size:.85rem;">
                            <?= htmlspecialchars($t['description'] ?: '—') ?>
                        </td>
                        <td><span class="badge badge-<?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></span></td>
                        <td><span class="badge badge-<?= $t['status'] ?>"><?= ucwords(str_replace('_',' ',$t['status'])) ?></span></td>
                        <td class="<?= $isOverdue ? 'overdue' : '' ?>">
                            <?= $t['due_date'] ? date('M j, Y', strtotime($t['due_date'])) : '—' ?>
                            <?= $isOverdue ? ' ⚠' : '' ?>
                        </td>
                        <td>
                            <div class="action-group">
                                <?php if ($t['status'] !== 'completed'): ?>
                                <a href="tasks.php?complete=<?= $t['id'] ?>"
                                   class="btn btn-success btn-sm"
                                   onclick="return confirm('Mark as complete?')"
                                   title="Mark Complete">✓</a>
                                <?php endif; ?>
                                <a href="#" class="btn btn-secondary btn-sm"
                                   onclick="openEditModal(<?= htmlspecialchars(json_encode($t)) ?>)"
                                   title="Edit">✎</a>
                                <a href="tasks.php?delete=<?= $t['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this task? This cannot be undone.')"
                                   title="Delete">✕</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ADD TASK MODAL -->
<div class="modal-overlay <?= $show_add ? 'open' : '' ?>" id="addModal" onclick="closeOnOverlay(event,'addModal')">
    <div class="modal">
        <div class="modal-header">
            <h3>Add New Task</h3>
            <button class="modal-close" onclick="closeModal('addModal')">✕</button>
        </div>
        <form method="POST" action="tasks.php">
            <input type="hidden" name="_action" value="add">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="What needs to be done?" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Optional details…"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="low">🟢 Low</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="pending" selected>Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" min="<?= $today ?>">
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Add Task</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TASK MODAL -->
<div class="modal-overlay" id="editModal" onclick="closeOnOverlay(event,'editModal')">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <button class="modal-close" onclick="closeModal('editModal')">✕</button>
        </div>
        <form method="POST" action="tasks.php">
            <input type="hidden" name="_action" value="update">
            <input type="hidden" name="task_id" id="edit_task_id">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" id="edit_title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" id="edit_priority">
                        <option value="low">🟢 Low</option>
                        <option value="medium">🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" id="edit_due_date">
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
function closeOnOverlay(e, id) {
    if (e.target === document.getElementById(id)) closeModal(id);
}
function openEditModal(task) {
    document.getElementById('edit_task_id').value    = task.id;
    document.getElementById('edit_title').value      = task.title;
    document.getElementById('edit_description').value= task.description || '';
    document.getElementById('edit_priority').value   = task.priority;
    document.getElementById('edit_status').value     = task.status;
    document.getElementById('edit_due_date').value   = task.due_date || '';
    openModal('editModal');
}
// Auto-open add modal if ?action=add
<?php if ($show_add): ?>
openModal('addModal');
<?php endif; ?>
</script>
</body>
</html>
