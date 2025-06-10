<?php
session_start();
require 'backend/auth_check.php';
require 'backend/data_fetch.php';
require 'backend/project_functions.php';
require 'backend/user_functions.php';

$db = new SQLite3('database.db');
$user_id = $_SESSION['user_id'];
$user = fetchUserData($db, $user_id);
$role = $user['role'];
$company_id = $user['company_id'];

// Получаем список проектов для выпадающего меню
$projects = fetchProjects($db, $user_id);
$all_projects = [];
while ($project = $projects->fetchArray(SQLITE3_ASSOC)) {
    $all_projects[] = $project;
}

// Получаем список пользователей для выпадающего меню
$users_okei = fetchUsers($db, $user_id);
$users_list = [];
while ($user_row = $users_okei->fetchArray(SQLITE3_ASSOC)) {
    $users_list[] = $user_row;
}

$preselected_project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

$error_create_task = handleCreateTask($db, $project_id, $tasks_name, $task_description, $importance, $progress, $deadline, $user_id_task, $checklist_items = [], $tag = null);

$current_page = basename($_SERVER['PHP_SELF']);
include('header.php');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание новой задачи</title>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <link href="styles.css" rel="stylesheet">
    <script src="js/main.js" defer></script>
    <script src="js/filters.js" defer></script>
    <script src="js/modals.js" defer></script>
    <script src="js/ajax.js" defer></script>
</head>
<body>
    <div class="edit-container">
        <p id="name_task_edit">Создание новой задачи</p>
        
        <?php if (isset($error_create_task)): ?>
            <p style="color: red; text-align: center; margin-top: -10px;"><?= $error_create_task ?></p>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="project_id">Проект:</label>
<select name="project_id" class="pattern_input" required>
    <?php foreach ($all_projects as $project): ?>
        <option value="<?= $project['id'] ?>" <?= $project['id'] == $preselected_project_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($project['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
            
            <div class="form-group">
                <label for="tasks_name">Название задачи:</label>
                <input type="text" name="tasks_name" id="tasks_name" class="pattern_input" 
                       placeholder="до 15 символов" required maxlength="15">
            </div>
            
            <div class="form-group">
                <label for="task_description">Описание:</label>
                <textarea name="task_description" rows="4" 
                          placeholder="до 500 символов" required maxlength="500"></textarea>
            </div>
            
            <div class="form-group">
                <label for="importance">Важность:</label>
                <select name="importance" class="pattern_input" required>
                    <option value="low">Низкая</option>
                    <option value="medium">Средняя</option>
                    <option value="high">Высокая</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="user_id">Исполнитель:</label>
                <select name="user_id" class="pattern_input" required>
                    <?php foreach ($users_list as $user_row): ?>
                        <option value="<?= $user_row['id'] ?>"><?= htmlspecialchars($user_row['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="progress">Прогресс (0-100%):</label>
                <input type="number" name="progress" min="0" max="100" class="pattern_input"
                       value="0" required>
            </div>
            
            <div class="form-group">
                <label for="deadline">Срок выполнения:</label>
                <input type="date" name="deadline" class="pattern_input" required>
            </div>
            
            <div class="form-group">
                <label for="tag">Тег:</label>
                <select name="tag" class="pattern_input">
                    <option value="">Без тега</option>
                    <option value="IT">IT</option>
                    <option value="Дизайн">Дизайн</option>
                    <option value="Маркетинг">Маркетинг</option>
                    <option value="Аналитика">Аналитика</option>
                    <option value="Продажи">Продажи</option>
                    <option value="Копирайтинг">Копирайтинг</option>
                </select>
            </div>

            <div class="form-group">
                <label for="task_file">Прикрепить файл <br>(до 1MB)</label>
                <input type="file" name="task_file" id="task_file" accept=".txt,.docx,.pdf">
            </div>
            
            <!-- Чек-лист -->
            <div class="checklist-container">
                <div class="checklist-title_edit" onclick="toggleChecklist(this)">
                    <label>Чек-лист (не обязательно)</label>
                    <span class="checklist-arrow">▼</span>
                </div>
                <div class="checklist-container" style="display: none;">
                    <div class="checklist-inputs">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="text" name="checklist_item_<?= $i ?>" class="pattern_input" 
                                   placeholder="Пункт <?= $i ?> (до 20 символов)" maxlength="20"><br>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit" name="create_task" id="change_project" class="pattern_button_2">Добавить</button>
            </div>
        </form>
        
        <a href="project.php" class="back-link">← К проектам</a>
    </div>
</body>
</html>