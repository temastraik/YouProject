<?php
function translateRole($role) {
    switch ($role) {
        case 'manager':
            return 'менеджер';
        case 'executer':
            return 'исполнитель';
        default:
            return $role;
    }
}

function displayTask($task, $db, $users_okei, $is_completed = false, $is_manager = false) {
    // Получение информации об исполнителе
    $users = $db->prepare("SELECT username FROM Users WHERE id = :user_id");
    $users->bindParam(':user_id', $task['user_id'], SQLITE3_INTEGER);
    $user_result = $users->execute();
    $userID = $user_result->fetchArray(SQLITE3_ASSOC);
    
    // Получение чек-листа для задачи
    $checklist_stmt = $db->prepare("SELECT * FROM Checklists WHERE task_id = :task_id");
    $checklist_stmt->bindParam(':task_id', $task['id'], SQLITE3_INTEGER);
    $checklist_result = $checklist_stmt->execute();
    $checklist_items = [];
    while ($item = $checklist_result->fetchArray(SQLITE3_ASSOC)) {
        $checklist_items[] = $item;
    }
    
    // Иконки важности задач
    $importance_icons = [
        'high' => ['svg' => 'svg_high', 'text' => 'Важно'],
        'medium' => ['svg' => 'svg_medium', 'text' => 'Подождет'],
        'low' => ['svg' => 'svg_low', 'text' => 'Последнее']
    ];
    $importance = $importance_icons[$task['importance']] ?? $importance_icons['low'];
    
    // Блок задачи
    if ($task['progress'] === 100) {
        echo '<div class="block_task_100">';
        echo "<p id='block_task_name_100'>" . htmlspecialchars($task['name']) . "</p>";
        echo '<pre>';
        echo "<p id='block_task_name_worker_100'>@" . htmlspecialchars($userID['username']) . "</p>";
        echo '<pre>';
        if (!empty($task['tag'])) {
            echo "<p id='block_task_tag_100'>#" . htmlspecialchars($task['tag']) . "</p>";
            echo '<pre>';
        }
        echo '<a id="block_task_href_100" href="task_view.php?id=' . $task['id'] . '">Описание...</a>';
        echo '</div>';
    } else {
        echo '<div class="block_task">';
        echo "<p id='block_task_name'>" . htmlspecialchars($task['name']) . "</p>";
        echo '<pre>';
        echo "<img src='SVG/importants.svg' id='{$importance['svg']}'>";
        echo "<p id='important_{$task['importance']}'>{$importance['text']}</p>";
        echo '<pre>';
        echo "<p id='block_task_name_worker'>@" . htmlspecialchars($userID['username']) . "</p>";
        echo '<pre>';
        echo "<p id='block_task_progress'>Прогресс: " . htmlspecialchars($task['progress']) . "%</p>";
        echo '<pre>';
        if (!empty($task['tag'])) {
            echo "<p id='block_task_tag'>#" . htmlspecialchars($task['tag']) . "</p>";
            echo '<pre>';
        }
        echo '<a id="block_task_href" href="task_view.php?id=' . $task['id'] . '">Описание...</a>';
        echo '</div>';
    }
}
?>