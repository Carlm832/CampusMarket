<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$currentUserId = currentUserId();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function isValidProductConversation(PDO $pdo, int $productId, int $currentUserId, int $otherUserId): bool {
    if ($productId <= 0 || $currentUserId <= 0 || $otherUserId <= 0 || $currentUserId === $otherUserId) {
        return false;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM products WHERE id = :pid");
    $stmt->execute([':pid' => $productId]);
    $sellerId = (int) $stmt->fetchColumn();

    return $sellerId > 0
        && ($currentUserId === $sellerId || $otherUserId === $sellerId);
}

if ($action === 'fetch') {
    $productId = (int)($_GET['product_id'] ?? 0);
    $otherUserId = (int)($_GET['other_user_id'] ?? 0);
    
    if (!$productId || !$otherUserId) {
        echo json_encode(['error' => 'Missing parameters']);
        exit;
    }

    if (!isValidProductConversation($pdo, $productId, $currentUserId, $otherUserId)) {
        echo json_encode(['error' => 'Invalid conversation context']);
        exit;
    }
    
    // Mark messages sent to me as read
    $stmtRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = :uid AND sender_id = :other AND product_id = :pid AND is_read = 0");
    $stmtRead->execute([
        ':uid' => $currentUserId,
        ':other' => $otherUserId,
        ':pid' => $productId
    ]);
    
    // Fetch messages
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id
        WHERE m.product_id = :pid
          AND (
              (m.sender_id = :uid1 AND m.receiver_id = :other1) OR
              (m.sender_id = :other2 AND m.receiver_id = :uid2)
          )
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([
        ':pid' => $productId,
        ':uid1' => $currentUserId,
        ':other1' => $otherUserId,
        ':other2' => $otherUserId,
        ':uid2' => $currentUserId
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response
    $results = [];
    foreach ($messages as $msg) {
        $results[] = [
            'id' => $msg['id'],
            'body' => htmlspecialchars($msg['body']),
            'is_mine' => $msg['sender_id'] == $currentUserId,
            'sender_name' => $msg['sender_name'],
            'created_at' => date('g:i A, M j', strtotime($msg['created_at']))
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $results]);
    exit;
}

if ($action === 'send') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $body = sanitize($_POST['body'] ?? '');
    
    if (!$productId || !$receiverId || empty($body)) {
        echo json_encode(['error' => 'Missing or empty parameters']);
        exit;
    }

    if (!isValidProductConversation($pdo, $productId, $currentUserId, $receiverId)) {
        echo json_encode(['error' => 'Invalid conversation context']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, product_id, body) VALUES (:sid, :rid, :pid, :body)");
        $stmt->execute([
            ':sid' => $currentUserId,
            ':rid' => $receiverId,
            ':pid' => $productId,
            ':body' => $body
        ]);
        
        // Notify receiver
        createNotification($pdo, $receiverId, 'message', "New Message", "You received a new message.", $productId);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
