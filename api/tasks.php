<?php
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$db = get_db();

if ($method === 'GET') {
    $status = $_GET['status'] ?? 'active';
    $type = $_GET['type'] ?? 'stock';
    
    $where = [];
    $params = [];

    if($type === 'fixed'){
        $where[] = 'is_fixed = 1';
        if(!empty($_GET['date'])){
            $where[] = 'scheduled_date = ?';
            $params[] = $_GET['date'];
        }
    }else{
        $where[] = 'is_fixed = 0';
    }

    if($status ==='completed'){
        $where[] = 'is_completed =1';
    }else{
        $where[] = 'is_completed=0'; 
    }

    $sql = 'SELECT * FROM tasks WHERE '. implode(' AND ',$where) . ' ORDER BY created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll());

} elseif ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $content = trim($body['content'] ?? '');
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'contentは必須です']);
        exit;
    }
    $is_fixed = (int)($body['is_fixed'] ?? 0);
    $scheduled_date = isset($body['scheduled_date'])
        ? trim((string)$body['scheduled_date'])
        : null;
    if($scheduled_date === ''){
        $scheduled_date = null;
    }
    if($is_fixed === 1 && $scheduled_date === null){
        http_response_code(400);
        echo json_encode(['error' => '日付は必須です']);
        exit;
    }

    $stmt = $db->prepare('INSERT INTO tasks (content,is_fixed,scheduled_date) VALUES (?,?,?)');
    $stmt->execute([$content,$is_fixed,$scheduled_date]);
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
