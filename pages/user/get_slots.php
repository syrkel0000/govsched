<?php
require_once '../../includes/db.php';

header('Content-Type: application/json');

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;
$date      = $_GET['date'] ?? '';

if (!$branch_id || !$date) {
    echo json_encode([]);
    exit();
}

$day = date('N', strtotime($date));
if ($day >= 6) {
    echo json_encode(['weekend' => true]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        ts.id,
        ts.slot_time,
        ts.max_capacity,
        COUNT(a.id) AS booked
    FROM time_slots ts
    LEFT JOIN appointments a 
        ON a.slot_id = ts.id 
        AND a.appointment_date = ?
        AND a.branch_id = ?
        AND a.status != 'cancelled'
    WHERE ts.branch_id = ?
    GROUP BY ts.id, ts.slot_time, ts.max_capacity
    ORDER BY ts.slot_time
");
$stmt->execute([$date, $branch_id, $branch_id]);
$slots = $stmt->fetchAll();

$result = [];
foreach ($slots as $slot) {
    $remaining = $slot['max_capacity'] - $slot['booked'];
    $result[] = [
        'id'        => $slot['id'],
        'slot_time' => $slot['slot_time'],
        'remaining' => max(0, $remaining),
        'full'      => $remaining <= 0
    ];
}

echo json_encode($result);