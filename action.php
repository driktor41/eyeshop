<?php
require '../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

// Ловим любые ошибки, чтобы вернуть их в JSON, а не в белый экран
set_error_handler(function($no, $str) {
    throw new Exception($str);
});

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Не авторизован']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $userId = (int)$_SESSION['user_id'];
    
    // =============== КЛИК ===============
    if ($action === 'click') {
        $stmt = $pdo->prepare("UPDATE users SET gold = gold + 1, eyes_count = eyes_count + 1 WHERE id = ?");
        $stmt->execute([$userId]);
        
        $stmt = $pdo->prepare("SELECT gold, eyes_count FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'gold' => (float)$user['gold'],
            'eyes' => (int)$user['eyes_count']
        ]);
        exit;
    }
    
    // =============== ПОКУПКА ===============
    if ($action === 'buy') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некорректный ID предмета']);
            exit;
        }
        
        // Получаем предмет
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Предмет не найден (id=' . $itemId . ')']);
            exit;
        }
        
        // Получаем пользователя
        $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
            exit;
        }
        
        if ((float)$user['gold'] < (float)$item['cost']) {
            echo json_encode([
                'success' => false,
                'message' => 'Недостаточно золота. Нужно: ' . $item['cost'] . ', у тебя: ' . $user['gold']
            ]);
            exit;
        }
        
        // Транзакция
        $pdo->beginTransaction();
        
        // 1. Списываем золото
        $stmt = $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?");
        $stmt->execute([$item['cost'], $userId]);
        
        // 2. Проверяем: есть ли уже такой предмет у юзера
        $stmt = $pdo->prepare("SELECT id, quantity FROM user_items WHERE user_id = ? AND item_id = ?");
        $stmt->execute([$userId, $itemId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Обновляем
            $stmt = $pdo->prepare("UPDATE user_items SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$existing['id']]);
        } else {
            // Вставляем
            $stmt = $pdo->prepare("INSERT INTO user_items (user_id, item_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$userId, $itemId]);
        }
        
        $pdo->commit();
        
        // Возвращаем новый баланс
        $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newUser = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'gold' => (float)$newUser['gold'],
            'item_name' => $item['name']
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Неизвестное действие: ' . $action]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
?>