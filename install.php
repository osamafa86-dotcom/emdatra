<?php
/**
 * emdatra — one-time setup.
 * Connects to your existing MySQL database, creates the emd_* tables,
 * your admin account, and writes config.php (which stays only on the server).
 * Delete this file after a successful install.
 */
session_start();

$CONFIG_PATH = __DIR__ . '/config.php';
$errors  = [];
$success = false;
$old = ['db_host' => 'localhost', 'db_name' => '', 'db_user' => '', 'admin_user' => 'admin'];

if (empty($_SESSION['icsrf'])) {
    $_SESSION['icsrf'] = bin2hex(random_bytes(16));
}

function ee($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** Locked if config exists, connects, and an admin already exists. */
function already_installed($path)
{
    if (!file_exists($path)) return false;
    $cfg = @include $path;
    if (!is_array($cfg) || empty($cfg['db'])) return false;
    $d = $cfg['db'];
    try {
        $pdo = new PDO(
            "mysql:host={$d['host']};dbname={$d['name']};charset=utf8mb4",
            $d['user'], $d['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return (int)$pdo->query('SELECT COUNT(*) FROM emd_admins')->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$locked = already_installed($CONFIG_PATH);

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['icsrf'] ?? '', $_POST['csrf'] ?? '')) {
        $errors[] = 'انتهت الجلسة. حدّث الصفحة وأعد المحاولة.';
    } else {
        $old['db_host']    = trim($_POST['db_host'] ?? 'localhost');
        $old['db_name']    = trim($_POST['db_name'] ?? '');
        $old['db_user']    = trim($_POST['db_user'] ?? '');
        $old['admin_user'] = trim($_POST['admin_user'] ?? '');
        $db_pass     = $_POST['db_pass'] ?? '';
        $admin_pass  = $_POST['admin_pass'] ?? '';
        $admin_pass2 = $_POST['admin_pass2'] ?? '';

        if ($old['db_name'] === '' || $old['db_user'] === '') $errors[] = 'اسم القاعدة واسم المستخدم مطلوبان.';
        if ($old['admin_user'] === '') $errors[] = 'اسم مستخدم لوحة التحكم مطلوب.';
        if (strlen($admin_pass) < 8)   $errors[] = 'كلمة مرور لوحة التحكم يجب ألا تقل عن 8 أحرف.';
        if ($admin_pass !== $admin_pass2) $errors[] = 'كلمتا مرور لوحة التحكم غير متطابقتين.';

        $pdo = null;
        if (!$errors) {
            try {
                $pdo = new PDO(
                    "mysql:host={$old['db_host']};dbname={$old['db_name']};charset=utf8mb4",
                    $old['db_user'], $db_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } catch (PDOException $e) {
                $errors[] = 'تعذّر الاتصال بقاعدة البيانات: ' . $e->getMessage();
            }
        }

        if (!$errors && $pdo) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS emd_admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(64) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $pdo->exec("CREATE TABLE IF NOT EXISTS emd_settings (
                    skey VARCHAR(64) PRIMARY KEY,
                    svalue MEDIUMTEXT
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $pdo->exec("CREATE TABLE IF NOT EXISTS emd_blocks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    page VARCHAR(64) NOT NULL DEFAULT 'home',
                    type VARCHAR(48) NOT NULL,
                    sort_order INT NOT NULL DEFAULT 0,
                    is_visible TINYINT(1) NOT NULL DEFAULT 1,
                    content MEDIUMTEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_page_order (page, sort_order)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                if ((int)$pdo->query('SELECT COUNT(*) FROM emd_admins')->fetchColumn() === 0) {
                    $stmt = $pdo->prepare('INSERT INTO emd_admins (username, password_hash) VALUES (?, ?)');
                    $stmt->execute([$old['admin_user'], password_hash($admin_pass, PASSWORD_DEFAULT)]);
                }

                $configArr = [
                    'db' => [
                        'host'    => $old['db_host'],
                        'name'    => $old['db_name'],
                        'user'    => $old['db_user'],
                        'pass'    => $db_pass,
                        'charset' => 'utf8mb4',
                    ],
                    'site' => ['base_url' => 'https://emdatra.emdatra.org', 'default_lang' => 'ar'],
                ];
                $php = "<?php\nreturn " . var_export($configArr, true) . ";\n";

                if (@file_put_contents($CONFIG_PATH, $php) === false) {
                    $errors[] = 'تم إنشاء الجداول، لكن تعذّرت كتابة config.php. تأكد أن مجلد الموقع قابل للكتابة (الصلاحية 755).';
                } else {
                    $success = true;
                }
            } catch (PDOException $e) {
                $errors[] = 'خطأ أثناء تجهيز قاعدة البيانات: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>إعداد لوحة تحكم emdatra</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Cairo',system-ui,Segoe UI,sans-serif;background:#14171C;color:#E7E9ED;min-height:100vh;display:grid;place-items:center;padding:24px;line-height:1.7}
  .card{width:min(540px,100%);background:#1C2128;border:1px solid #2A313A;border-radius:18px;padding:34px;box-shadow:0 30px 70px rgba(0,0,0,.4)}
  h1{font-size:24px;color:#fff;margin-bottom:6px}
  .sub{color:#9CA3AD;font-size:14px;margin-bottom:22px}
  .grp{margin-bottom:16px}
  label{display:block;font-size:14px;font-weight:600;color:#fff;margin-bottom:7px}
  input{width:100%;padding:13px 14px;border-radius:11px;background:#14171C;border:1.5px solid #2A313A;color:#fff;font:inherit}
  input:focus{outline:none;border-color:#FF6B1A}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .hr{height:1px;background:#2A313A;margin:22px 0}
  .sec{font-size:13px;color:#FF8A4C;font-weight:700;margin-bottom:14px;letter-spacing:.3px}
  button{width:100%;padding:15px;border:none;border-radius:11px;background:#FF6B1A;color:#fff;font-weight:700;font-size:16px;cursor:pointer;margin-top:8px}
  button:hover{background:#D94A04}
  .msg{padding:13px 15px;border-radius:11px;font-size:14px;margin-bottom:16px}
  .err{background:rgba(229,72,77,.12);border:1px solid #E5484D;color:#ff9ea1}
  .ok{background:rgba(22,137,78,.14);border:1px solid #16894e;color:#7ee2a8}
  .note{background:rgba(255,107,26,.1);border:1px solid #FF6B1A;color:#ffb98c;font-size:13px}
  code{background:#14171C;padding:2px 7px;border-radius:6px;color:#FF8A4C;font-size:13px}
  a{color:#FF8A4C}
  ul{margin:8px 18px}
</style>
</head>
<body>
<div class="card">
  <h1>إعداد لوحة تحكم emdatra</h1>
  <p class="sub">خطوة لمرة واحدة: ربط قاعدة بياناتك وإنشاء حساب الدخول.</p>

  <?php if ($locked): ?>
    <div class="msg ok">✅ النظام مُثبَّت بالفعل.</div>
    <p>لوحة التحكم جاهزة. لأسباب أمنية <strong>احذف ملف <code>install.php</code></strong> الآن من مدير الملفات.</p>
    <p style="margin-top:10px">لإعادة الإعداد من الصفر، احذف ملف <code>config.php</code> أولًا.</p>

  <?php elseif ($success): ?>
    <div class="msg ok">✅ تم الإعداد بنجاح! أُنشئت الجداول وحساب لوحة التحكم وملف <code>config.php</code>.</div>
    <div class="msg note">
      🔒 مهم جدًا: <strong>احذف ملف <code>install.php</code></strong> الآن من مدير الملفات (cPanel ← File Manager) لحماية موقعك.
    </div>
    <p>الخطوة التالية من جهتي: بناء صفحة الدخول ولوحة التحكم ونقل محتوى موقعك. اكتب لي <strong>«نجح الإعداد»</strong>.</p>

  <?php else: ?>
    <?php foreach ($errors as $er): ?>
      <div class="msg err"><?= ee($er) ?></div>
    <?php endforeach; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= ee($_SESSION['icsrf']) ?>">

      <div class="sec">① بيانات قاعدة البيانات (الموجودة لديك)</div>
      <div class="grp">
        <label>المضيف (Host)</label>
        <input type="text" name="db_host" value="<?= ee($old['db_host']) ?>" placeholder="localhost">
      </div>
      <div class="grp">
        <label>اسم قاعدة البيانات</label>
        <input type="text" name="db_name" value="<?= ee($old['db_name']) ?>" placeholder="مثل z4sww4_emdatra" required>
      </div>
      <div class="grp">
        <label>اسم مستخدم قاعدة البيانات</label>
        <input type="text" name="db_user" value="<?= ee($old['db_user']) ?>" placeholder="مثل z4sww4_emdatra" required>
      </div>
      <div class="grp">
        <label>كلمة مرور قاعدة البيانات</label>
        <input type="password" name="db_pass" placeholder="••••••••">
      </div>

      <div class="hr"></div>

      <div class="sec">② حساب الدخول للوحة التحكم (تختاره أنت)</div>
      <div class="grp">
        <label>اسم المستخدم</label>
        <input type="text" name="admin_user" value="<?= ee($old['admin_user']) ?>" required>
      </div>
      <div class="row">
        <div class="grp">
          <label>كلمة المرور</label>
          <input type="password" name="admin_pass" placeholder="8 أحرف على الأقل" required>
        </div>
        <div class="grp">
          <label>تأكيد كلمة المرور</label>
          <input type="password" name="admin_pass2" required>
        </div>
      </div>

      <button type="submit">تثبيت الآن</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
