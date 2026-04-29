<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
requireApplicant();

$date = $_GET['date'] ?? '';
$office = $_GET['office'] ?? '';

if (!$date || !$office) {
    echo json_encode([]);
    exit();
}

// Block weekends
$day = date('N', strtotime($date));
if ($day >= 6) {
    echo json_encode(['weekend' => true]);
    exit();
}

// Get slots for this office with booking count
$stmt = $pdo->prepare("
    SELECT t.id, t.slot_time, t.max_capacity,
           COUNT(a.id) as booked
    FROM time_slots t
    LEFT JOIN appointments a 
        ON a.slot_id = t.id 
        AND a.appointment_date = ? 
        AND a.office = ?
        AND a.status != 'cancelled'
    WHERE t.office = ?
    GROUP BY t.id
    ORDER BY t.id
");
$stmt->execute([$date, $office, $office]);
$slots = $stmt->fetchAll();

$result = [];
foreach ($slots as $slot) {
    $remaining = $slot['max_capacity'] - $slot['booked'];
    $result[] = [
        'id'        => $slot['id'],
        'slot_time' => $slot['slot_time'],
        'booked'    => (int)$slot['booked'],
        'capacity'  => $slot['max_capacity'],
        'remaining' => $remaining,
        'full'      => $remaining <= 0
    ];
}

header('Content-Type: application/json');
echo json_encode($result);
?>