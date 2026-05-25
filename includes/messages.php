<?php
/**
 * Contact messages: storage layer.
 * The table is created on demand so it works without a separate migration.
 */

require_once __DIR__ . '/db.php';

function ensure_messages_table()
{
    static $done = false;
    if ($done) return;
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(60) DEFAULT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            ip VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}

function save_message($name, $email, $phone, $message, $ip = null)
{
    ensure_messages_table();
    $stmt = db()->prepare(
        'INSERT INTO emd_messages (name, email, phone, message, ip) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone, $message, $ip]);
    return (int) db()->lastInsertId();
}

function get_messages()
{
    ensure_messages_table();
    return db()->query('SELECT * FROM emd_messages ORDER BY created_at DESC, id DESC')->fetchAll();
}

function unread_message_count()
{
    ensure_messages_table();
    return (int) db()->query('SELECT COUNT(*) FROM emd_messages WHERE is_read = 0')->fetchColumn();
}

function mark_message_read($id, $read = true)
{
    db()->prepare('UPDATE emd_messages SET is_read = ? WHERE id = ?')->execute([$read ? 1 : 0, (int) $id]);
}

function mark_all_messages_read()
{
    ensure_messages_table();
    db()->exec('UPDATE emd_messages SET is_read = 1 WHERE is_read = 0');
}

function delete_message($id)
{
    db()->prepare('DELETE FROM emd_messages WHERE id = ?')->execute([(int) $id]);
}
