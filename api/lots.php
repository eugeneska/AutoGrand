<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$dbPath = __DIR__ . '/../db/autogrand.db';

if (!file_exists($dbPath)) {
    echo json_encode(['lots' => []]);
    exit;
}

$db = new SQLite3($dbPath);
$result = $db->query("SELECT id, title, body_type, engine, year, price, image FROM lots WHERE is_active = 1 ORDER BY created_at DESC");

$lots = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['image_url'] = './uploads/' . $row['image'];
    $lots[] = $row;
}

echo json_encode(['lots' => $lots], JSON_UNESCAPED_UNICODE);
