<?php
/* =========================================================
   emdatra — contact form handler
   Receives the contact form (POST), validates, and emails it.
   ---------------------------------------------------------
   CHANGE THIS to the address where you want to receive messages:
   ========================================================= */
$TO   = 'info@emdatra.com';
$FROM = 'no-reply@emdatra.org';      // should be a mailbox on your own domain
/* ========================================================= */

require_once __DIR__ . '/includes/chat.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

// Honeypot: bots fill this hidden field — silently accept and drop.
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$phone   = trim($_POST['phone']   ?? '');
$message = trim($_POST['message'] ?? '');

// Server-side validation
if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid']);
    exit;
}

// Basic header-injection guard
foreach ([$name, $email, $phone] as $v) {
    if (preg_match('/[\r\n]/', $v)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'invalid']);
        exit;
    }
}

$body  = "رسالة جديدة من نموذج التواصل في موقع emdatra\n";
$body .= "----------------------------------------\n";
$body .= "الاسم: {$name}\n";
$body .= "البريد الإلكتروني: {$email}\n";
$body .= "رقم الهاتف: " . ($phone !== '' ? $phone : '—') . "\n\n";
$body .= "الرسالة:\n{$message}\n";

$subject = '=?UTF-8?B?' . base64_encode('رسالة جديدة من الموقع — ' . $name) . '?=';

$headers  = "From: emdatra Website <{$FROM}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Persist as a conversation (primary record); email is a best-effort notice.
$saved = false;
try {
    $conv = chat_new_conversation($name, $email, $phone, 'form');
    chat_add_message($conv['id'], 'user', $message);
    $saved = true;
} catch (Throwable $e) {
    error_log('emdatra: message save failed — ' . $e->getMessage());
}

$sent = @mail($TO, $subject, $body, $headers);

echo json_encode(['ok' => ($saved || $sent)]);
