<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) redirect('dashboard.php');

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name (maximum 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $insert->execute([$name, $email, $hash]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            set_flash('success', 'Your account has been created. Welcome!');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
    <div class="auth-card">
        <p class="eyebrow">GET ORGANIZED</p>
        <h1>Create your account</h1>
        <p class="muted">A little structure goes a long way. Start tracking your tasks.</p>
        <?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <label>Full name<input name="name" value="<?= e($name) ?>" maxlength="100" required autocomplete="name"></label>
            <label>Email address<input type="email" name="email" value="<?= e($email) ?>" maxlength="150" required autocomplete="email"></label>
            <label>Password<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
            <label>Confirm password<input type="password" name="confirm_password" minlength="8" required autocomplete="new-password"></label>
            <button class="btn btn-primary btn-full" type="submit">Create account</button>
        </form>
        <p class="auth-switch">Already registered? <a href="login.php">Log in</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
