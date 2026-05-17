<?php
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = get_db();

if ($method === 'GET') {
    $status = $_GET['status'] ?? 'active';
    if ($status === 'completed') {
        $stmt = $db->query('SELECT * FROM tasks WHERE is_completed = 1 ORDER BY created_at DESC');
    } else {
        $stmt = $db->query('SELECT * FROM tasks WHERE is_completed = 0 ORDER BY created_at DESC');
    }
    echo json_encode($stmt->fetchAll());

} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $content = trim($body['content'] ?? '');
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'contentは必須です']);
        exit;
    }
    $stmt = $db->prepare('INSERT INTO tasks (content) VALUES (?)');
    $stmt->execute([$content]);
    echo json_encode(['id' => $db->lastInsertId()]);

} elseif ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'idは必須です']);
        exit;
    }
    $stmt = $db->prepare('DELETE FROM tasks WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['ok' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
