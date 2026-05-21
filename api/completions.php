<?php
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$task_id = (int)($body['task_id'] ?? 0);
$content = trim($body['content'] ?? '');
$reason  = trim($body['reason'] ?? '');
$is_fixed = (int)($body['is_fixed'] ?? 0);

if ($task_id === 0 || $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'task_idとcontentは必須です']);
    exit;
}

$db = get_db();
$db->prepare('INSERT INTO completions (task_id, content, reason,is_fixed) VALUES (?, ?, ?,?)')->execute([$task_id, $content, $reason,$is_fixed]);
$db->prepare('UPDATE tasks SET is_completed = 1 WHERE id = ?')->execute([$task_id]);

echo json_encode(['ok' => true]);
