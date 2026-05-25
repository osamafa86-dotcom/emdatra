<?php
/* =========================================================
   emdatra — live chat endpoint (visitor side, JSON API)
   Actions: start | send | poll | history | seen
   ========================================================= */
$NOTIFY_TO   = 'info@emdatra.com';        // where new-conversation alerts go
$NOTIFY_FROM = 'no-reply@emdatra.org';    // a mailbox on your own domain
$MAXLEN      = 4000;

require_once __DIR__ . '/includes/chat.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out($a)
{
    echo json_encode($a, JSON_UNESCAPED_UNICODE);
    exit;
}

function chat_notify_admin($conv, $body)
{
    global $NOTIFY_TO, $NOTIFY_FROM;
    $name  = $conv['name'] ?? '';
    $email = $conv['email'] ?? '';
    $phone = $conv['phone'] ?? '';

    $text  = "محادثة جديدة بدأت على موقع emdatra\n";
    $text .= "----------------------------------------\n";
    $text .= "الاسم: {$name}\n";
    $text .= "البريد: " . ($email !== '' ? $email : '—') . "\n";
    $text .= "الهاتف: " . ($phone !== '' ? $phone : '—') . "\n\n";
    $text .= "الرسالة:\n{$body}\n\n";
    $text .= "افتح لوحة التحكم للرد: admin/messages.php\n";

    $subject = '=?UTF-8?B?' . base64_encode('محادثة جديدة — ' . $name) . '?=';
    $headers = "From: emdatra <{$NOTIFY_FROM}>\r\n";
    if ($email !== '' && !preg_match('/[\r\n]/', $email)) {
        $headers .= "Reply-To: {$name} <{$email}>\r\n";
    }
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    @mail($NOTIFY_TO, $subject, $text, $headers);
}

$action = $_REQUEST['action'] ?? '';

/* ---------- start a new conversation ---------- */
if ($action === 'start') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'method']);
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $body  = trim($_POST['message'] ?? '');

    if ($name === '' || mb_strlen($name) > 150) out(['ok' => false, 'error' => 'name']);
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) out(['ok' => false, 'error' => 'email']);
    if ($email === '' && $phone === '') out(['ok' => false, 'error' => 'contact']);
    if (preg_match('/[\r\n]/', $name . $email . $phone)) out(['ok' => false, 'error' => 'invalid']);

    $conv = chat_new_conversation($name, $email, $phone, 'chat');
    $messages = [];
    if ($body !== '') {
        $body = mb_substr($body, 0, $MAXLEN);
        $id = chat_add_message($conv['id'], 'user', $body);
        $messages[] = chat_msg_json(['id' => $id, 'sender' => 'user', 'body' => $body, 'created_at' => date('Y-m-d H:i:s')]);
        chat_notify_admin(['name' => $name, 'email' => $email, 'phone' => $phone], $body);
    }
    out(['ok' => true, 'token' => $conv['token'], 'name' => $name, 'messages' => $messages, 'unread' => 0]);
}

/* ---------- send a message in an existing conversation ---------- */
if ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'method']);
    $conv = chat_by_token($_POST['token'] ?? '');
    if (!$conv) out(['ok' => false, 'error' => 'token']);
    $body = trim($_POST['message'] ?? '');
    if ($body === '') out(['ok' => false, 'error' => 'empty']);
    $body = mb_substr($body, 0, $MAXLEN);

    $first = count(chat_messages($conv['id'], 0)) === 0;
    $id = chat_add_message($conv['id'], 'user', $body);
    if ($first) chat_notify_admin($conv, $body);

    out(['ok' => true, 'message' => chat_msg_json(['id' => $id, 'sender' => 'user', 'body' => $body, 'created_at' => date('Y-m-d H:i:s')])]);
}

/* ---------- poll for new messages ---------- */
if ($action === 'poll') {
    $conv = chat_by_token($_GET['token'] ?? '');
    if (!$conv) out(['ok' => false, 'error' => 'token']);
    $after = (int) ($_GET['after'] ?? 0);
    $rows  = chat_messages($conv['id'], $after);
    if (!empty($_GET['seen'])) chat_seen_by_user($conv['id']);
    out(['ok' => true, 'messages' => array_map('chat_msg_json', $rows), 'unread' => chat_user_unread($conv['id'])]);
}

/* ---------- load full history (resume) ---------- */
if ($action === 'history') {
    $conv = chat_by_token($_GET['token'] ?? '');
    if (!$conv) out(['ok' => false, 'error' => 'token']);
    $rows = chat_messages($conv['id'], 0);
    out(['ok' => true, 'name' => $conv['name'], 'messages' => array_map('chat_msg_json', $rows), 'unread' => chat_user_unread($conv['id'])]);
}

/* ---------- mark admin replies as seen ---------- */
if ($action === 'seen') {
    $conv = chat_by_token($_POST['token'] ?? '');
    if (!$conv) out(['ok' => false, 'error' => 'token']);
    chat_seen_by_user($conv['id']);
    out(['ok' => true]);
}

out(['ok' => false, 'error' => 'unknown']);
