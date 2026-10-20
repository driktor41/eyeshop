<?php
require __DIR__ . '/../includes/config.php';

$action = $_GET['action'] ?? 'login';

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: auth.php?action=login');
    exit;
}

// Если пользователь уже залогинен и не пытается выйти — редирект на главную
if (isset($_SESSION['user_id']) && $action !== 'logout') {
    header('Location: ../index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($action === 'register') {
        if (mb_strlen($username) < 3 || mb_strlen($password) < 4) {
            $error = 'Логин мин. 3 символа, пароль мин. 4';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->fetch()) {
                $error = 'Такой пользователь уже есть';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, password, gold, eyes_count) VALUES (?, ?, 50, 0)"
                );
                $stmt->execute([$username, $hash]);
                $_SESSION['user_id'] = (int)$pdo->lastInsertId();
                header('Location: ../index.php');
                exit;
            }
        }
    } elseif ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            header('Location: ../index.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $action === 'register' ? 'Регистрация' : 'Вход' ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="auth-container">
    <h2><?= $action === 'register' ? 'Регистрация' : 'Вход' ?></h2>

    <?php if ($error !== null): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Логин" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit"><?= $action === 'register' ? 'Создать' : 'Войти' ?></button>
    </form>

    <p>
        <?php if ($action === 'register'): ?>
            <a href="?action=login">Уже есть аккаунт? Войти</a>
        <?php else: ?>
            <a href="?action=register">Нет аккаунта? Зарегистрироваться</a>
        <?php endif; ?>
    </p>
</div>
</body>
</html>