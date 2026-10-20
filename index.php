<?php
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: api/auth.php?action=login');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, gold, eyes_count FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: api/auth.php?action=login');
    exit;
}

// Получаем предметы + сколько у юзера уже есть
$stmt = $pdo->prepare("
    SELECT i.*, COALESCE(ui.quantity, 0) AS owned
    FROM items i
    LEFT JOIN user_items ui ON ui.item_id = i.id AND ui.user_id = ?
    ORDER BY i.cost ASC
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>The Unblinking Eye — Shop</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Немигающее Око</h1>
            <div class="stats">
                <span id="gold-display">Золото: <?= number_format((float)$user['gold'], 2) ?></span>
                <span id="eyes-display">Очей: <?= (int)$user['eyes_count'] ?></span>
                <span class="username"><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <a href="api/auth.php?action=logout" class="logout">Выйти</a>
        </header>

        <main>
            <div class="click-area">
                <button id="click-btn">Собрать Око</button>
                <p>Каждый клик даёт +1 золото. Покупай артефакты для усиления.</p>
            </div>

            <div class="shop-grid">
                <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <p><?= htmlspecialchars($item['description']) ?></p>
                    <p class="cost">Цена: <?= number_format((float)$item['cost'], 2) ?> золота</p>
                    <p class="owned">В наличии: <?= (int)$item['owned'] ?></p>
                    <button 
                        class="buy-btn" 
                        data-id="<?= (int)$item['id'] ?>" 
                        data-name="<?= htmlspecialchars($item['name']) ?>">
                        Купить
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>