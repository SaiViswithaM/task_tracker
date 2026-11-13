<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
require_once __DIR__ . '/config/database.php';

$statuses = ['Pending', 'In Progress', 'Completed'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$task = ['title' => '', 'description' => '', 'status' => 'Pending'];
$isEdit = $id !== null;

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT id, title, description, status FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, current_user_id()]);
    $found = $stmt->fetch();
    if (!$found) {
        http_response_code(404);
        set_flash('error', 'Task not found or you do not have permission to edit it.');
        redirect('dashboard.php');
    }
    $task = $found;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Pending';

    if ($title === '') $errors[] = 'Task title is required.';
    if (mb_strlen($title) > 255) $errors[] = 'Task title must be 255 characters or fewer.';
    if (!in_array($status, $statuses, true)) $errors[] = 'Choose a valid task status.';

    $task = ['title' => $title, 'description' => $description, 'status' => $status];

    if (!$errors) {
        if ($isEdit) {
            $update = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?');
            $update->execute([$title, $description, $status, $id, current_user_id()]);
            set_flash('success', 'Task updated successfully.');
        } else {
            $insert = $pdo->prepare('INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)');
            $insert->execute([current_user_id(), $title, $description, $status]);
            set_flash('success', 'Task added successfully.');
        }
        redirect('dashboard.php');
    }
}

$pageTitle = $isEdit ? 'Edit task' : 'Add task';
require __DIR__ . '/includes/header.php';
?>
<section class="form-page">
    <a class="back-link" href="dashboard.php">← Back to dashboard</a>
    <div class="panel form-panel">
        <p class="eyebrow"><?= $isEdit ? 'MAKE AN UPDATE' : 'NEW ITEM' ?></p>
        <h1><?= $isEdit ? 'Edit task' : 'Create a task' ?></h1>
        <p class="muted">Add the details below so you know exactly what needs to get done.</p>
        <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Task title<input name="title" maxlength="255" value="<?= e($task['title']) ?>" required autofocus placeholder="e.g. Complete project documentation"></label>
            <label>Description<textarea name="description" rows="5" placeholder="Add a few details (optional)"><?= e($task['description']) ?></textarea></label>
            <label>Status<select name="status"><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $task['status'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></label>
            <div class="form-actions"><a class="btn btn-quiet" href="dashboard.php">Cancel</a><button class="btn btn-primary" type="submit"><?= $isEdit ? 'Save changes' : 'Add task' ?></button></div>
        </form>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
