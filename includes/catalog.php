<?php
/**
 * Product / commodity catalog storage layer (created on demand).
 */
require_once __DIR__ . '/db.php';

function ensure_products_table()
{
    static $done = false;
    if ($done) return;
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name_ar VARCHAR(180) NOT NULL,
            name_en VARCHAR(180) DEFAULT NULL,
            category_ar VARCHAR(120) DEFAULT NULL,
            category_en VARCHAR(120) DEFAULT NULL,
            origin VARCHAR(120) DEFAULT NULL,
            moq VARCHAR(80) DEFAULT NULL,
            unit_ar VARCHAR(60) DEFAULT NULL,
            unit_en VARCHAR(60) DEFAULT NULL,
            desc_ar TEXT,
            desc_en TEXT,
            image VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}

function products_all($activeOnly = false)
{
    ensure_products_table();
    $sql = 'SELECT * FROM emd_products';
    if ($activeOnly) $sql .= ' WHERE is_active = 1';
    $sql .= ' ORDER BY sort_order ASC, id DESC';
    return db()->query($sql)->fetchAll();
}

function product_by_id($id)
{
    ensure_products_table();
    $stmt = db()->prepare('SELECT * FROM emd_products WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    return $stmt->fetch() ?: null;
}

function product_create($d)
{
    ensure_products_table();
    $stmt = db()->prepare(
        'INSERT INTO emd_products (name_ar, name_en, category_ar, category_en, origin, moq, unit_ar, unit_en, desc_ar, desc_en, image, is_active, sort_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $d['name_ar'], $d['name_en'], $d['category_ar'], $d['category_en'], $d['origin'], $d['moq'],
        $d['unit_ar'], $d['unit_en'], $d['desc_ar'], $d['desc_en'], $d['image'], $d['is_active'], $d['sort_order'],
    ]);
    return (int) db()->lastInsertId();
}

function product_update($id, $d)
{
    ensure_products_table();
    $stmt = db()->prepare(
        'UPDATE emd_products SET name_ar=?, name_en=?, category_ar=?, category_en=?, origin=?, moq=?, unit_ar=?, unit_en=?, desc_ar=?, desc_en=?, image=?, is_active=?, sort_order=? WHERE id=?'
    );
    $stmt->execute([
        $d['name_ar'], $d['name_en'], $d['category_ar'], $d['category_en'], $d['origin'], $d['moq'],
        $d['unit_ar'], $d['unit_en'], $d['desc_ar'], $d['desc_en'], $d['image'], $d['is_active'], $d['sort_order'], (int) $id,
    ]);
}

function product_delete($id)
{
    ensure_products_table();
    db()->prepare('DELETE FROM emd_products WHERE id = ?')->execute([(int) $id]);
}

function product_toggle_active($id)
{
    ensure_products_table();
    db()->prepare('UPDATE emd_products SET is_active = 1 - is_active WHERE id = ?')->execute([(int) $id]);
}

/** Distinct categories among active products, for the public filter. */
function product_categories()
{
    ensure_products_table();
    $rows = db()->query('SELECT DISTINCT category_ar, category_en FROM emd_products WHERE is_active = 1 AND category_ar <> "" ORDER BY category_ar')->fetchAll();
    return $rows ?: [];
}
