<?php
/* =========================================================
   emdatra — quote request handler
   Validates a structured quote form and stores it as a
   conversation (source "quote") in the shared inbox.
   ========================================================= */
$NOTIFY_TO   = 'info@emdatra.com';
$NOTIFY_FROM = 'no-reply@emdatra.org';

require_once __DIR__ . '/includes/chat.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

// Honeypot.
if (!empty($_POST['website'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$name        = trim($_POST['name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$product     = trim($_POST['product'] ?? '');
$quantity    = trim($_POST['quantity'] ?? '');
$origin      = trim($_POST['origin'] ?? '');
$destination = trim($_POST['destination'] ?? '');
$message     = trim($_POST['message'] ?? '');

// Required: name, product, and at least one contact method.
if ($name === '' || $product === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'invalid']);
    exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'email']);
    exit;
}
if ($email === '' && $phone === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'contact']);
    exit;
}
// Header-injection guard on single-line fields.
foreach ([$name, $email, $phone, $product, $quantity, $origin, $destination] as $v) {
    if (preg_match('/[\r\n]/', $v)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'invalid']);
        exit;
    }
}

// Build the structured message body.
$lines = ['📋 طلب عرض سعر', '----------------------------------------'];
$lines[] = 'المنتج / الخدمة: ' . $product;
if ($quantity !== '')    $lines[] = 'الكمية: ' . $quantity;
if ($origin !== '')      $lines[] = 'بلد المصدر: ' . $origin;
if ($destination !== '') $lines[] = 'بلد الوجهة: ' . $destination;
if ($message !== '')     $lines[] = "\nتفاصيل إضافية:\n" . $message;
$body = mb_substr(implode("\n", $lines), 0, 4000);

$saved = false;
try {
    $conv = chat_new_conversation($name, $email, $phone, 'quote');
    chat_add_message($conv['id'], 'user', $body);
    $saved = true;
} catch (Throwable $e) {
    error_log('emdatra: quote save failed — ' . $e->getMessage());
}

// Best-effort email notification.
$subject = '=?UTF-8?B?' . base64_encode('طلب عرض سعر — ' . $name) . '?=';
$headers = "From: emdatra <{$NOTIFY_FROM}>\r\n";
if ($email !== '') $headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
$mailBody = $body . "\n\n----------------------------------------\n"
    . "الاسم: {$name}\nالبريد: " . ($email !== '' ? $email : '—') . "\nالهاتف: " . ($phone !== '' ? $phone : '—') . "\n";
$sent = @mail($NOTIFY_TO, $subject, $mailBody, $headers);

echo json_encode(['ok' => ($saved || $sent)]);
