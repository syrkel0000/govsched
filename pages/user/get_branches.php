<?php
require_once '../../includes/db.php';

header('Content-Type: application/json');

$document_id = isset($_GET['document_id']) ? (int)$_GET['document_id'] : 0;

if (!$document_id) {
    echo json_encode([]);
    exit();
}

// Get agency of selected document
$stmt = $pdo->prepare("SELECT agency FROM documents WHERE id = ?");
$stmt->execute([$document_id]);
$doc = $stmt->fetch();

if (!$doc) {
    echo json_encode([]);
    exit();
}

// Get all branches for that agency
$stmt = $pdo->prepare("SELECT id, name, address, contact, city FROM branches WHERE agency = ? ORDER BY city, name");
$stmt->execute([$doc['agency']]);
$branches = $stmt->fetchAll();

echo json_encode($branches);