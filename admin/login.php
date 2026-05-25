<?php
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM emd_admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        login_admin($admin);
        redirect('index.php');
    }
    usleep(400000);
    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
}

$brand = setting('brand', ['name' => 'emdatra', 'logo' => '../assets/logo.png']);
$logo  = '../' . ($brand['logo'] ?? 'assets/logo.png');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>دخول لوحة التحكم — emdatra</title>
<link rel="icon" type="image/png" href="<?= esc($logo) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="auth">
  <div class="auth__card">
    <div class="auth__logo">
      <img src="<?= esc($logo) ?>" alt="emdatra">
      <b>emdatra</b>
    </div>
    <p class="auth__sub">لوحة التحكم — تسجيل الدخول</p>

    <?php if ($error): ?>
      <div class="msg msg--err"><?= esc($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <?= csrf_field() ?>
      <div class="field">
        <label>اسم المستخدم</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="field">
        <label>كلمة المرور</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn--block">دخول</button>
    </form>
  </div>
</div>
</body>
</html>
