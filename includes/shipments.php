<?php
/**
 * Shipment tracking storage layer.
 * Two tables (created on demand): shipments and their timeline events.
 * Public visitors look up a shipment by its tracking number; the admin
 * manages shipments and pushes status updates from the panel.
 */

require_once __DIR__ . '/db.php';

/** Status keys in their natural order, with bilingual labels. */
function shipment_statuses()
{
    return [
        'booked'           => ['ar' => 'تم الحجز',            'en' => 'Booked'],
        'picked_up'        => ['ar' => 'تم الاستلام',          'en' => 'Picked up'],
        'in_transit'       => ['ar' => 'قيد الشحن',            'en' => 'In transit'],
        'at_customs'       => ['ar' => 'في التخليص الجمركي',   'en' => 'At customs'],
        'arrived'          => ['ar' => 'وصلت بلد الوجهة',      'en' => 'Arrived'],
        'out_for_delivery' => ['ar' => 'قيد التسليم',          'en' => 'Out for delivery'],
        'delivered'        => ['ar' => 'تم التسليم',           'en' => 'Delivered'],
        'on_hold'          => ['ar' => 'معلّقة',               'en' => 'On hold'],
    ];
}
function shipment_status_label($key, $lang = 'ar')
{
    $s = shipment_statuses();
    return $s[$key][$lang] ?? $key;
}
function shipment_valid_status($key)
{
    return array_key_exists($key, shipment_statuses());
}

function ensure_shipments_tables()
{
    static $done = false;
    if ($done) return;
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_shipments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tracking_no VARCHAR(40) NOT NULL,
            customer_name VARCHAR(150) DEFAULT NULL,
            origin VARCHAR(120) DEFAULT NULL,
            destination VARCHAR(120) DEFAULT NULL,
            description VARCHAR(255) DEFAULT NULL,
            mode VARCHAR(20) NOT NULL DEFAULT "sea",
            status VARCHAR(30) NOT NULL DEFAULT "booked",
            eta DATE DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_tracking (tracking_no)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS emd_shipment_events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            shipment_id INT UNSIGNED NOT NULL,
            status VARCHAR(30) NOT NULL,
            location VARCHAR(120) DEFAULT NULL,
            note VARCHAR(255) DEFAULT NULL,
            event_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY k_ship (shipment_id, event_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $done = true;
}

function shipment_modes()
{
    return ['sea' => ['ar' => 'بحري', 'en' => 'Sea'], 'air' => ['ar' => 'جوي', 'en' => 'Air'], 'land' => ['ar' => 'بري', 'en' => 'Land']];
}
function shipment_mode_label($key, $lang = 'ar')
{
    $m = shipment_modes();
    return $m[$key][$lang] ?? $key;
}

function shipment_normalize_tracking($no)
{
    return strtoupper(trim(preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $no)));
}

function shipment_suggest_tracking()
{
    return 'EMD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
}

function shipment_by_id($id)
{
    ensure_shipments_tables();
    $stmt = db()->prepare('SELECT * FROM emd_shipments WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $id]);
    return $stmt->fetch() ?: null;
}

function shipment_by_tracking($no)
{
    ensure_shipments_tables();
    $no = shipment_normalize_tracking($no);
    if ($no === '') return null;
    $stmt = db()->prepare('SELECT * FROM emd_shipments WHERE tracking_no = ? LIMIT 1');
    $stmt->execute([$no]);
    return $stmt->fetch() ?: null;
}

function shipment_all()
{
    ensure_shipments_tables();
    return db()->query('SELECT * FROM emd_shipments ORDER BY updated_at DESC, id DESC')->fetchAll();
}

function shipment_create($data)
{
    ensure_shipments_tables();
    $stmt = db()->prepare(
        'INSERT INTO emd_shipments (tracking_no, customer_name, origin, destination, description, mode, status, eta)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['tracking_no'], $data['customer_name'], $data['origin'], $data['destination'],
        $data['description'], $data['mode'], $data['status'], $data['eta'] !== '' ? $data['eta'] : null,
    ]);
    return (int) db()->lastInsertId();
}

function shipment_update($id, $data)
{
    ensure_shipments_tables();
    $stmt = db()->prepare(
        'UPDATE emd_shipments SET tracking_no=?, customer_name=?, origin=?, destination=?, description=?, mode=?, status=?, eta=?, updated_at=CURRENT_TIMESTAMP WHERE id=?'
    );
    $stmt->execute([
        $data['tracking_no'], $data['customer_name'], $data['origin'], $data['destination'],
        $data['description'], $data['mode'], $data['status'], $data['eta'] !== '' ? $data['eta'] : null, (int) $id,
    ]);
}

function shipment_delete($id)
{
    ensure_shipments_tables();
    db()->prepare('DELETE FROM emd_shipment_events WHERE shipment_id = ?')->execute([(int) $id]);
    db()->prepare('DELETE FROM emd_shipments WHERE id = ?')->execute([(int) $id]);
}

function shipment_tracking_exists($no, $exceptId = 0)
{
    ensure_shipments_tables();
    $stmt = db()->prepare('SELECT COUNT(*) FROM emd_shipments WHERE tracking_no = ? AND id <> ?');
    $stmt->execute([$no, (int) $exceptId]);
    return (int) $stmt->fetchColumn() > 0;
}

function shipment_add_event($shipmentId, $status, $location, $note, $eventAt)
{
    ensure_shipments_tables();
    $stmt = db()->prepare(
        'INSERT INTO emd_shipment_events (shipment_id, status, location, note, event_at) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([(int) $shipmentId, $status, $location, $note, $eventAt]);
    // Reflect the latest event as the shipment's current status.
    db()->prepare('UPDATE emd_shipments SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
        ->execute([$status, (int) $shipmentId]);
    return (int) db()->lastInsertId();
}

function shipment_events($shipmentId)
{
    ensure_shipments_tables();
    $stmt = db()->prepare('SELECT * FROM emd_shipment_events WHERE shipment_id = ? ORDER BY event_at DESC, id DESC');
    $stmt->execute([(int) $shipmentId]);
    return $stmt->fetchAll();
}

function shipment_delete_event($eventId)
{
    ensure_shipments_tables();
    db()->prepare('DELETE FROM emd_shipment_events WHERE id = ?')->execute([(int) $eventId]);
}

/** Public-safe view for the tracking widget (no customer identity). */
function shipment_public($no)
{
    $s = shipment_by_tracking($no);
    if (!$s) return null;
    $events = array_map(function ($e) {
        return [
            'status'   => $e['status'],
            'status_ar' => shipment_status_label($e['status'], 'ar'),
            'status_en' => shipment_status_label($e['status'], 'en'),
            'location' => $e['location'],
            'note'     => $e['note'],
            'at'       => date('Y/m/d H:i', strtotime($e['event_at'])),
        ];
    }, shipment_events($s['id']));

    return [
        'tracking_no' => $s['tracking_no'],
        'origin'      => $s['origin'],
        'destination' => $s['destination'],
        'mode'        => $s['mode'],
        'mode_ar'     => shipment_mode_label($s['mode'], 'ar'),
        'mode_en'     => shipment_mode_label($s['mode'], 'en'),
        'status'      => $s['status'],
        'status_ar'   => shipment_status_label($s['status'], 'ar'),
        'status_en'   => shipment_status_label($s['status'], 'en'),
        'eta'         => $s['eta'],
        'events'      => $events,
    ];
}
