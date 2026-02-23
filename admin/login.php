<?php
require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE login = :login");
    $stmt->bindValue(':login', $login, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['login'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — AutoGrand Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #052555 0%, #116DFF 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card h1 {
            font-size: 28px;
            font-weight: 800;
            color: #111318;
            text-align: center;
            margin-bottom: 8px;
        }
        .login-card p {
            font-size: 14px;
            color: #5F6673;
            text-align: center;
            margin-bottom: 32px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #111318;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #E3E8F0;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #116DFF; }
        .error {
            background: #FEE2E2;
            color: #E53935;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #052555 0%, #116DFF 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>AUTOGRAND</h1>
        <p>Панель управления</p>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Войти</button>
        </form>
    </div>
</body>
</html>
