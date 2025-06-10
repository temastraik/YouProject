<?php
// Получение ID задачи
$task_id = $_GET['id'] ?? 0;

// Получение информации о задаче
$stmt = $db->prepare("SELECT * FROM Tasks WHERE id = :task_id");
$stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
$task = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$task) {
    die("Задача не найдена");
}

// Получение списка чек-поинтов для задачи
$checklist_stmt = $db->prepare("SELECT * FROM Checklists WHERE task_id = :task_id");
$checklist_stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
$checklist_result = $checklist_stmt->execute();

// Получение списка пользователей для выбора исполнителя (только для менеджеров)
$users_stmt = $db->prepare("SELECT id, username FROM Users WHERE company_id = (SELECT company_id FROM Users WHERE id = :user_id)");
$users_stmt->bindParam(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
$users_result = $users_stmt->execute();

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Обработка чек-поинтов (доступно и для исполнителей)
    if (isset($_POST['checklist'])) {
        foreach ($_POST['checklist'] as $item_id => $is_checked) {
            $checked_value = $is_checked; // теперь это будет 0 или 1
            $update_stmt = $db->prepare("UPDATE Checklists SET is_checked = :is_checked WHERE id = :id");
            $update_stmt->bindParam(':is_checked', $checked_value, SQLITE3_INTEGER);
            $update_stmt->bindParam(':id', $item_id, SQLITE3_INTEGER);
            $update_stmt->execute();
        }
    }

    if ($user['role'] === 'manager') {
        // Для менеджеров - полное редактирование
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $importance = $_POST['importance'];
        $user_id = $_POST['user_id'];
        $progress = $_POST['progress'];
        $deadline = $_POST['deadline'];
        $tag = !empty($_POST['tag']) ? $_POST['tag'] : null;

        $stmt = $db->prepare("UPDATE Tasks SET name = :name, description = :description, importance = :importance, 
                             user_id = :user_id, progress = :progress, deadline = :deadline, tag = :tag
                             WHERE id = :task_id");
        $stmt->bindParam(':name', $name, SQLITE3_TEXT);
        $stmt->bindParam(':description', $description, SQLITE3_TEXT);
        $stmt->bindParam(':importance', $importance, SQLITE3_TEXT);
        $stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindParam(':progress', $progress, SQLITE3_INTEGER);
        $stmt->bindParam(':deadline', $deadline, SQLITE3_TEXT);
        $stmt->bindParam(':tag', $tag, SQLITE3_TEXT);
        $stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
    } else {
        // Для исполнителей - только прогресс
        $progress = $_POST['progress'];
        
        $stmt = $db->prepare("UPDATE Tasks SET progress = :progress WHERE id = :task_id");
        $stmt->bindParam(':progress', $progress, SQLITE3_INTEGER);
        $stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
    }
    
    if ($stmt->execute()) {
        header('Location: task_view.php?id=' . $task_id);
        exit;
    } else {
        $error = "Ошибка при обновлении задачи";
    }
}
?>