<?php
require_once 'backend/auth_check.php';
require_once 'functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
include('header.php');

function getImportanceData($importance) {
    $importance_icons = [
        'high' => ['svg' => 'svg_high_description', 'text' => 'Важно'],
        'medium' => ['svg' => 'svg_medium_description', 'text' => 'Подождет'],
        'low' => ['svg' => 'svg_low_description', 'text' => 'Последнее']
    ];
    return $importance_icons[$importance] ?? $importance_icons['low'];
}

// Получение ID задачи
$task_id = $_GET['id'] ?? 0;

// Получение информации о задаче
$stmt = $db->prepare("SELECT * FROM Tasks WHERE id = :task_id");
$stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
$task = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$task) {
    die("Задача не найдена");
}

// Получение информации об исполнителе
$stmt = $db->prepare("SELECT username, id FROM Users WHERE id = :user_id");
$stmt->bindParam(':user_id', $task['user_id'], SQLITE3_INTEGER);
$user_result = $stmt->execute();
$user = $user_result->fetchArray(SQLITE3_ASSOC);

// Получение чек-листа
$checklist_stmt = $db->prepare("SELECT * FROM Checklists WHERE task_id = :task_id");
$checklist_stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
$checklist_result = $checklist_stmt->execute();
$checklist_items = [];
while ($item = $checklist_result->fetchArray(SQLITE3_ASSOC)) {
    $checklist_items[] = $item;
}

$importance = getImportanceData($task['importance']);

// Получение информации о пользователе для модального окна
if ($user) {
    $stmt = $db->prepare("SELECT username, first_name, last_name, patronymic, email FROM Users WHERE id = :id");
    $stmt->bindParam(':id', $user['id'], SQLITE3_INTEGER);
    $user_info = $stmt->execute();
    $user_data = $user_info->fetchArray(SQLITE3_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр задачи - <?= htmlspecialchars($task['name']) ?></title>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
</head>
<body>
    <div class="task-container">
        <p class="pattern_heading"><?= htmlspecialchars($task['name']) ?></p>
        
        <div class="task_info">
            <p id="task_info_description"><strong>Описание:</strong> <?= htmlspecialchars($task['description']) ?></p>
            <p><strong>Важность:</strong></p><img src="SVG/importants.svg" id="<?= $importance['svg']?>">
            <p id="<?= 'description_important_' . $task['importance']; ?>"><?php echo $importance['text']; ?></p>
            <p><strong>Исполнитель:</strong><a href="#" onclick="document.getElementById('user_info_<?= $user['id']; ?>').style.display='block'"><?= htmlspecialchars($user['username']); ?></a></p>
            <p><strong>Прогресс:</strong> <?= htmlspecialchars($task['progress']) ?>%</p>
            <p><strong>Срок выполнения:</strong> <?= htmlspecialchars($task['deadline']) ?></p>
            <p><strong>Тег:</strong> <span>#<?= htmlspecialchars($task['tag']) ?></span></p>
            
            <?php if (!empty($task['file_path']) && file_exists($task['file_path'])): ?>
                <p><strong>Прикрепленный файл:</strong></p>
                <?php
                    $file_name = basename($task['file_path']);
                    // Удаляем уникальный префикс для отображения оригинального имени файла
                    $display_name = preg_replace('/^[a-z0-9]+_/', '', $file_name);
                ?>
                <a href="download_file.php?task_id=<?= $task_id ?>" class="file-download">
                    <?= htmlspecialchars($display_name) ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($checklist_items)): ?>
            <div class="checklist-container">
                <div class="checklist-title">Чек-лист:</div>
                <?php foreach ($checklist_items as $item): ?>
                    <div class="checklist-item">
                        <input type="checkbox" <?= $item['is_checked'] ? 'checked' : '' ?> disabled>
                        <label><?= htmlspecialchars($item['item_text']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="task_edit.php?id=<?php echo htmlspecialchars($task['id'], ENT_QUOTES); ?>" class="change_task">Изменить</a>
        <form method="POST" action="delete_task.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить задачу \'<?= addslashes($task['name']) ?>\'?')">
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
            <button type="submit" id="delete_project">Удалить задачу</button>
        </form>

        <?php if ($user && $user_data): ?>
            <div id="user_info_<?php echo $user['id']; ?>" class="modal" style="display:none;">
                <div id="window_user_info" class="pattern_modal">
                    <a onclick="document.getElementById('user_info_<?php echo $user['id']; ?>').style.display='none'"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
                    <p id="name_user_info" class="pattern_heading">Профиль <?php echo htmlspecialchars($user['username']); ?></p>
                    <p id="window_user_info_p"><strong>Фамилия:</strong> <?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?></p>
                    <p id="window_user_info_p"><strong>Имя:</strong> <?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?></p>
                    <p id="window_user_info_p"><strong>Отчество:</strong> <?php echo htmlspecialchars($user_data['patronymic'] ?? ''); ?></p>
                    <p id="window_user_info_p"><strong>Почта:</strong> <?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <a href="project.php" class="back-link">← К проектам</a>
    </div>
</body>
</html>