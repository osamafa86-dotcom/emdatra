<?php
/**
 * Shared helpers: session, escaping, CSRF, auth, settings, bilingual content.
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
}

/* ---------- output escaping ---------- */
function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function e($v) { echo esc($v); }

function redirect($url) { header('Location: ' . $url); exit; }

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . esc(csrf_token()) . '">'; }
function verify_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
            http_response_code(419);
            exit('Session expired or invalid token. Please go back and try again.');
        }
    }
}

/* ---------- auth ---------- */
function is_logged_in() { return !empty($_SESSION['admin_id']); }
function require_login()
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}
function login_admin(array $admin)
{
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_user'] = $admin['username'];
}
function logout_admin()
{
    $_SESSION = [];
    session_destroy();
}

/* ---------- settings (key -> JSON value) ---------- */
function all_settings()
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT skey, svalue FROM emd_settings')->fetchAll() as $row) {
            $decoded = json_decode($row['svalue'], true);
            $cache[$row['skey']] = ($decoded === null && $row['svalue'] !== 'null') ? $row['svalue'] : $decoded;
        }
    }
    return $cache;
}
function setting($key, $default = null)
{
    $all = all_settings();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}
function save_setting($key, $value)
{
    $json = json_encode($value, JSON_UNESCAPED_UNICODE);
    $stmt = db()->prepare(
        'INSERT INTO emd_settings (skey, svalue) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
    );
    $stmt->execute([$key, $json]);
}

/* ---------- blocks ---------- */
function get_blocks($page = 'home', $visible_only = false)
{
    $sql = 'SELECT * FROM emd_blocks WHERE page = ?';
    if ($visible_only) $sql .= ' AND is_visible = 1';
    $sql .= ' ORDER BY sort_order ASC, id ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute([$page]);
    return $stmt->fetchAll();
}
function block_data($block)
{
    $d = json_decode($block['content'] ?? '{}', true);
    return is_array($d) ? $d : [];
}

/* ---------- bilingual content ---------- *
 * Fields are stored as "<key>_ar" and "<key>_en" inside a block's JSON.
 * bil() prints data-ar/data-en attributes so the existing front-end JS
 * language switcher keeps working unchanged.
 */
function bil_val($data, $key, $lang)
{
    return $data[$key . '_' . $lang] ?? '';
}
function bil_attrs($data, $key)
{
    return 'data-ar="' . esc(bil_val($data, $key, 'ar')) . '" '
         . 'data-en="' . esc(bil_val($data, $key, 'en')) . '"';
}
function bil_text($data, $key, $lang = 'ar')
{
    return esc(bil_val($data, $key, $lang));
}
