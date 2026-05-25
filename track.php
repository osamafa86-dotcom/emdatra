<?php
/* =========================================================
   emdatra — public shipment tracking lookup (JSON)
   ========================================================= */
require_once __DIR__ . '/includes/shipments.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$no = $_GET['no'] ?? $_POST['no'] ?? '';
try {
    $data = shipment_public($no);
} catch (Throwable $e) {
    error_log('emdatra: track lookup failed — ' . $e->getMessage());
    $data = null;
}
echo json_encode($data ? ['ok' => true, 'shipment' => $data] : ['ok' => false], JSON_UNESCAPED_UNICODE);
