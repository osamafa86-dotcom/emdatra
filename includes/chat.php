<?php
/**
 * Live chat / conversations storage layer.
 * Two tables (created on demand): conversations and their messages.
 * A conversation is identified to the visitor by an unguessable token
 * kept in their browser; the admin sees every conversation in the panel.
 */

require_once __DIR__ . '/db.php';

function ensure_chat_tables()
{
    static $done = false;
    if ($done) return;
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_conversations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            token CHAR(40) NOT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(190) DEFAULT NULL,
            phone VARCHAR(60) DEFAULT NULL,
            source VARCHAR(10) NOT NULL DEFAULT "chat",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_chat_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            conversation_id INT UNSIGNED NOT NULL,
            sender VARCHAR(10) NOT NULL,
            body TEXT NOT NULL,
            read_by_admin TINYINT(1) NOT NULL DEFAULT 0,
            read_by_user TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY k_conv (conversation_id, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}

function chat_valid_token($t)
{
    return is_string($t) && preg_match('/^[a-f0-9]{40}$/', $t) === 1;
}

function chat_new_conversation($name, $email, $phone, $source = 'chat')
{
    ensure_chat_tables();
    $token = bin2hex(random_bytes(20));
    $stmt  = db()->prepare(
        'INSERT INTO emd_conversations (token, name, email, phone, source) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$token, $name, ($email !== '' ? $email : null), ($phone !== '' ? $phone : null), $source]);
    return ['id' => (int) db()->lastInsertId(), 'token' => $token];
}

function chat_by_token($token)
{
    ensure_chat_tables();
    if (!chat_valid_token($token)) return null;
    $stmt = db()->prepare('SELECT * FROM emd_conversations WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function chat_by_id($id)
{
    ensure_chat_tables();
    $stmt = db()->prepare('SELECT * FROM emd_conversations WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    return $stmt->fetch() ?: null;
}

function chat_add_message($convId, $sender, $body)
{
    ensure_chat_tables();
    $sender = $sender === 'admin' ? 'admin' : 'user';
    // A sender has implicitly "read" their own message.
    $readByAdmin = $sender === 'admin' ? 1 : 0;
    $readByUser  = $sender === 'user' ? 1 : 0;
    $stmt = db()->prepare(
        'INSERT INTO emd_chat_messages (conversation_id, sender, body, read_by_admin, read_by_user)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([(int) $convId, $sender, $body, $readByAdmin, $readByUser]);
    db()->prepare('UPDATE emd_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')
        ->execute([(int) $convId]);
    return (int) db()->lastInsertId();
}

function chat_messages($convId, $afterId = 0)
{
    ensure_chat_tables();
    $stmt = db()->prepare(
        'SELECT * FROM emd_chat_messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC'
    );
    $stmt->execute([(int) $convId, (int) $afterId]);
    return $stmt->fetchAll();
}

/** Mark admin replies as seen by the visitor. */
function chat_seen_by_user($convId)
{
    ensure_chat_tables();
    db()->prepare('UPDATE emd_chat_messages SET read_by_user = 1 WHERE conversation_id = ? AND sender = "admin"')
        ->execute([(int) $convId]);
}

/** Mark visitor messages as seen by the admin. */
function chat_seen_by_admin($convId)
{
    ensure_chat_tables();
    db()->prepare('UPDATE emd_chat_messages SET read_by_admin = 1 WHERE conversation_id = ? AND sender = "user"')
        ->execute([(int) $convId]);
}

/** Count admin replies the visitor has not seen yet (bubble badge). */
function chat_user_unread($convId)
{
    ensure_chat_tables();
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM emd_chat_messages WHERE conversation_id = ? AND sender = "admin" AND read_by_user = 0'
    );
    $stmt->execute([(int) $convId]);
    return (int) $stmt->fetchColumn();
}

/** Conversations with their latest message, for the admin inbox. */
function chat_conversations()
{
    ensure_chat_tables();
    return db()->query(
        'SELECT c.*,
            (SELECT m.body   FROM emd_chat_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_body,
            (SELECT m.sender FROM emd_chat_messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_sender,
            (SELECT COUNT(*) FROM emd_chat_messages m WHERE m.conversation_id = c.id AND m.sender = "user" AND m.read_by_admin = 0) AS unread
         FROM emd_conversations c
         ORDER BY c.updated_at DESC, c.id DESC'
    )->fetchAll();
}

/** Number of conversations awaiting an admin reply (sidebar badge). */
function chat_admin_unread_total()
{
    ensure_chat_tables();
    return (int) db()->query(
        'SELECT COUNT(DISTINCT conversation_id) FROM emd_chat_messages WHERE sender = "user" AND read_by_admin = 0'
    )->fetchColumn();
}

function chat_delete_conversation($id)
{
    ensure_chat_tables();
    db()->prepare('DELETE FROM emd_chat_messages WHERE conversation_id = ?')->execute([(int) $id]);
    db()->prepare('DELETE FROM emd_conversations WHERE id = ?')->execute([(int) $id]);
}

/** Human label for a conversation source (empty for normal chat). */
function chat_source_label($source)
{
    $map = ['form' => 'نموذج', 'quote' => 'عرض سعر'];
    return $map[$source] ?? '';
}

/** Serialize one message row for JSON responses. */
function chat_msg_json($row)
{
    return [
        'id'     => (int) $row['id'],
        'sender' => $row['sender'],
        'body'   => $row['body'],
        'time'   => date('H:i', strtotime($row['created_at'])),
    ];
}
