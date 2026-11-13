<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/config/database.php';

$allowedStatuses = ['Pending', 'In Progress', 'Completed'];
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
if (!in_array($status, $allowedStatuses, true)) $status = '';

$countsStmt = $pdo->prepare("SELECT COUNT(*) AS total,
    SUM(status = 'Pending') AS pending,
    SUM(status = 'In Progress') AS in_progress,
    SUM(status = 'Completed') AS completed
    FROM tasks WHERE user_id = ?");
$countsStmt->execute([current_user_id()]);
$counts = $countsStmt->fetch();

$sql = 'SELECT id, title, description, status, created_at FROM tasks WHERE user_id = :user_id';
$params = [':user_id' => current_user_id()];
if ($search !== '') {
    $sql .= ' AND title LIKE :search';
    $params[':search'] = '%' . $search . '%';
}
if ($status !== '') {
    $sql .= ' AND status = :status';
    $params[':status'] = $status;
}
$sql .= ' ORDER BY created_at DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">YOUR WORKSPACE</p>
        <h1>Task dashboard</h1>
        <p class="muted">Keep the important things moving, one task at a time.</p>
    </div>
    <a class="btn btn-primary" href="task_form.php">＋ Add new task</a>
</section>

<section class="stats-grid">
    <article class="stat-card"><span>Total tasks</span><strong><?= (int)($counts['total'] ?? 0) ?></strong></article>
    <article class="stat-card"><span>Pending</span><strong><?= (int)($counts['pending'] ?? 0) ?></strong></article>
    <article class="stat-card"><span>In progress</span><strong><?= (int)($counts['in_progress'] ?? 0) ?></strong></article>
    <article class="stat-card"><span>Completed</span><strong><?= (int)($counts['completed'] ?? 0) ?></strong></article>
</section>

<section class="panel">
    <form class="filters" method="get">
        <div class="search-field"><label class="sr-only" for="search">Search tasks</label><input id="search" name="search" placeholder="Search by task title..." value="<?= e($search) ?>"></div>
        <div><label class="sr-only" for="status">Filter by status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-secondary" type="submit">Apply filters</button>
        <a class="btn btn-quiet" href="dashboard.php">Clear</a>
    </form>

    <div class="section-title"><h2>Your tasks</h2><span class="muted"><?= count($tasks) ?> shown</span></div>
    <?php if (!$tasks): ?>
        <div class="empty-state"><div class="empty-icon">✦</div><h3>No tasks found</h3><p class="muted">Try changing your filters or add your first task.</p><a class="btn btn-primary" href="task_form.php">Create a task</a></div>
    <?php else: ?>
        <div class="task-list">
        <?php foreach ($tasks as $task): ?>
            <article class="task-row">
                <div class="task-main">
                    <div class="task-title-line"><h3><?= e($task['title']) ?></h3><span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= e($task['status']) ?></span></div>
                    <p><?= nl2br(e($task['description'])) ?></p>
                    <small>Created <?= e(date('M j, Y · g:i A', strtotime($task['created_at']))) ?></small>
                </div>
                <div class="task-actions">
                    <a class="btn btn-small btn-secondary" href="task_form.php?id=<?= (int)$task['id'] ?>">Edit</a>
                    <form method="post" action="delete_task.php" data-confirm="Delete this task? This action cannot be undone.">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                        <button class="btn btn-small btn-danger" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
