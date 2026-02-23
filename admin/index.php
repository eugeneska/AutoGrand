<?php
require_once __DIR__ . '/config.php';
requireAuth();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $body_type = trim($_POST['body_type'] ?? '');
        $engine = trim($_POST['engine'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $price = trim($_POST['price'] ?? '');

        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $imageName = uniqid('lot_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
            }
        }

        if ($title && $body_type && $engine && $year && $price && $imageName) {
            $stmt = $db->prepare("INSERT INTO lots (title, body_type, engine, year, price, image) VALUES (:title, :body_type, :engine, :year, :price, :image)");
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':body_type', $body_type, SQLITE3_TEXT);
            $stmt->bindValue(':engine', $engine, SQLITE3_TEXT);
            $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
            $stmt->bindValue(':price', $price, SQLITE3_TEXT);
            $stmt->bindValue(':image', $imageName, SQLITE3_TEXT);
            $stmt->execute();
            $message = 'Лот успешно добавлен';
            $messageType = 'success';
        } else {
            $message = 'Заполните все поля и загрузите изображение';
            $messageType = 'error';
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $img = $db->querySingle("SELECT image FROM lots WHERE id = $id");
            if ($img && file_exists(__DIR__ . '/../uploads/' . $img)) {
                unlink(__DIR__ . '/../uploads/' . $img);
            }
            $db->exec("DELETE FROM lots WHERE id = $id");
            $message = 'Лот удалён';
            $messageType = 'success';
        }
    }

    if ($action === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $db->exec("UPDATE lots SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = $id");
            $message = 'Статус обновлён';
            $messageType = 'success';
        }
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $body_type = trim($_POST['body_type'] ?? '');
        $engine = trim($_POST['engine'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $price = trim($_POST['price'] ?? '');

        if ($id && $title && $body_type && $engine && $year && $price) {
            $stmt = $db->prepare("UPDATE lots SET title=:title, body_type=:body_type, engine=:engine, year=:year, price=:price WHERE id=:id");
            $stmt->bindValue(':title', $title, SQLITE3_TEXT);
            $stmt->bindValue(':body_type', $body_type, SQLITE3_TEXT);
            $stmt->bindValue(':engine', $engine, SQLITE3_TEXT);
            $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
            $stmt->bindValue(':price', $price, SQLITE3_TEXT);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->execute();

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($ext, $allowed)) {
                    $oldImg = $db->querySingle("SELECT image FROM lots WHERE id = $id");
                    if ($oldImg && file_exists(__DIR__ . '/../uploads/' . $oldImg)) {
                        unlink(__DIR__ . '/../uploads/' . $oldImg);
                    }
                    $imageName = uniqid('lot_') . '.' . $ext;
                    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $imageName);
                    $db->exec("UPDATE lots SET image = '$imageName' WHERE id = $id");
                }
            }

            $message = 'Лот обновлён';
            $messageType = 'success';
        }
    }
}

$lots = [];
$result = $db->query("SELECT * FROM lots ORDER BY created_at DESC");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $lots[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — AutoGrand</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #F7F9FC; color: #111318; }

        .header {
            background: #fff;
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .header h1 { font-size: 20px; font-weight: 800; color: #116DFF; }
        .header-right { display: flex; align-items: center; gap: 16px; }
        .header-right span { font-size: 14px; color: #5F6673; }
        .btn-logout {
            padding: 8px 20px;
            background: #E53935;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .card h2 { font-size: 22px; font-weight: 700; margin-bottom: 24px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-grid .full { grid-column: 1 / -1; }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #5F6673; }
        .form-group input,
        .form-group select {
            padding: 12px 16px;
            border: 1px solid #E3E8F0;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Montserrat', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus { border-color: #116DFF; }

        .btn-submit {
            padding: 14px 32px;
            background: #116DFF;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #0A56CC; }

        .msg {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }
        .msg.success { background: #D1FAE5; color: #065F46; }
        .msg.error { background: #FEE2E2; color: #991B1B; }

        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: 700;
            color: #8B93A3;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #E3E8F0;
        }
        td {
            padding: 14px 16px;
            font-size: 14px;
            border-bottom: 1px solid #F0F2F5;
            vertical-align: middle;
        }
        tr:hover td { background: #F7F9FC; }

        .lot-img {
            width: 80px;
            height: 54px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active { background: #D1FAE5; color: #065F46; }
        .badge-inactive { background: #FEE2E2; color: #991B1B; }

        .actions { display: flex; gap: 8px; }
        .btn-sm {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
        }
        .btn-toggle { background: #EAF2FF; color: #116DFF; }
        .btn-edit { background: #FEF3C7; color: #92400E; }
        .btn-delete { background: #FEE2E2; color: #E53935; }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            width: 100%;
            max-width: 540px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal h3 { font-size: 20px; font-weight: 700; margin-bottom: 20px; }

        .empty { text-align: center; padding: 48px; color: #8B93A3; font-size: 16px; }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .header { padding: 12px 16px; }
            .container { padding: 16px; }
            .card { padding: 20px; }
            table { font-size: 13px; }
            .lot-img { width: 60px; height: 40px; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>AUTOGRAND</h1>
    <div class="header-right">
        <span><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></span>
        <a href="logout.php" class="btn-logout">Выйти</a>
    </div>
</div>

<div class="container">

    <?php if ($message): ?>
        <div class="msg <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Добавить лот</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <div class="form-group">
                    <label>Название автомобиля</label>
                    <input type="text" name="title" placeholder="BMW 5 Series" required>
                </div>
                <div class="form-group">
                    <label>Тип кузова</label>
                    <input type="text" name="body_type" placeholder="Седан" required>
                </div>
                <div class="form-group">
                    <label>Двигатель</label>
                    <input type="text" name="engine" placeholder="2.0L Turbo" required>
                </div>
                <div class="form-group">
                    <label>Год выпуска</label>
                    <input type="number" name="year" placeholder="2024" min="1990" max="2030" required>
                </div>
                <div class="form-group">
                    <label>Цена (₽)</label>
                    <input type="text" name="price" placeholder="от 2 850 000" required oninput="formatPrice(this)">
                </div>
                <div class="form-group">
                    <label>Фото</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="form-group full" style="margin-top: 8px;">
                    <button type="submit" class="btn-submit">Добавить лот</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Все лоты (<?= count($lots) ?>)</h2>
        <?php if (empty($lots)): ?>
            <div class="empty">Лотов пока нет. Добавьте первый!</div>
        <?php else: ?>
            <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Кузов</th>
                        <th>Двигатель</th>
                        <th>Год</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lots as $lot): ?>
                    <tr>
                        <td><img src="../uploads/<?= htmlspecialchars($lot['image']) ?>" class="lot-img" alt=""></td>
                        <td><strong><?= htmlspecialchars($lot['title']) ?></strong></td>
                        <td><?= htmlspecialchars($lot['body_type']) ?></td>
                        <td><?= htmlspecialchars($lot['engine']) ?></td>
                        <td><?= $lot['year'] ?></td>
                        <td><?= htmlspecialchars($lot['price']) ?></td>
                        <td>
                            <span class="badge <?= $lot['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $lot['is_active'] ? 'Активен' : 'Скрыт' ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $lot['id'] ?>">
                                    <button type="submit" class="btn-sm btn-toggle"><?= $lot['is_active'] ? 'Скрыть' : 'Показать' ?></button>
                                </form>
                                <button class="btn-sm btn-edit" onclick="openEdit(<?= htmlspecialchars(json_encode($lot)) ?>)">Изменить</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить этот лот?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $lot['id'] ?>">
                                    <button type="submit" class="btn-sm btn-delete">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>Редактировать лот</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="title" id="edit-title" required>
                </div>
                <div class="form-group">
                    <label>Тип кузова</label>
                    <input type="text" name="body_type" id="edit-body_type" required>
                </div>
                <div class="form-group">
                    <label>Двигатель</label>
                    <input type="text" name="engine" id="edit-engine" required>
                </div>
                <div class="form-group">
                    <label>Год выпуска</label>
                    <input type="number" name="year" id="edit-year" required>
                </div>
                <div class="form-group">
                    <label>Цена</label>
                    <input type="text" name="price" id="edit-price" required oninput="formatPrice(this)">
                </div>
                <div class="form-group">
                    <label>Новое фото (необязательно)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <div class="form-group full" style="margin-top: 8px; display: flex; flex-direction: row; gap: 12px;">
                    <button type="submit" class="btn-submit">Сохранить</button>
                    <button type="button" class="btn-submit" style="background: #8B93A3;" onclick="closeEdit()">Отмена</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(lot) {
    document.getElementById('edit-id').value = lot.id;
    document.getElementById('edit-title').value = lot.title;
    document.getElementById('edit-body_type').value = lot.body_type;
    document.getElementById('edit-engine').value = lot.engine;
    document.getElementById('edit-year').value = lot.year;
    document.getElementById('edit-price').value = lot.price;
    document.getElementById('editModal').classList.add('active');
}

function closeEdit() {
    document.getElementById('editModal').classList.remove('active');
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});

function formatPrice(input) {
    let val = input.value.replace(/\s*₽\s*$/, '').trim();
    if (val && !val.endsWith('₽')) {
        input.value = val + ' ₽';
        input.setSelectionRange(val.length, val.length);
    }
}
</script>

</body>
</html>
