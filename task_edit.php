<?php 
include 'backend/auth_check.php';
require 'task_edit_handler.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование задачи - <?= htmlspecialchars($task['name']) ?></title>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
</head>
<body>
    <div class="edit-container">
        <p id="name_task_edit">Редактирование задачи</p>
        
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Название задачи:</label>
                <input type="text" id="name" name="name" class="pattern_input" value="<?= htmlspecialchars($task['name']) ?>" 
                       placeholder="до 15 символов" required maxlength="15" 
                       <?= $user['role'] === 'executer' ? 'class="disabled-field" disabled' : '' ?>>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" rows="4" placeholder="до 500 символов" 
                          required maxlength="500" 
                          <?= $user['role'] === 'executer' ? 'class="disabled-field" disabled' : '' ?>><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="importance">Важность:</label>
                <select name="importance" class="pattern_input" required <?= $user['role'] === 'executer' ? 'class="disabled-field" disabled' : '' ?>>
                    <option value="low" <?= $task['importance'] === 'low' ? 'selected' : '' ?>>Низкая</option>
                    <option value="medium" <?= $task['importance'] === 'medium' ? 'selected' : '' ?>>Средняя</option>
                    <option value="high" <?= $task['importance'] === 'high' ? 'selected' : '' ?>>Высокая</option>
                </select>
            </div>
            
            <?php if ($user['role'] === 'manager'): ?>
                <div class="form-group">
                    <label for="tag">Тег:</label>
                    <select class="pattern_input" name="tag" <?= $user['role'] === 'executer' ? 'class="disabled-field" disabled' : '' ?>>
                        <option value="">Без тега</option>
                        <option value="IT" <?= $task['tag'] === 'IT' ? 'selected' : '' ?>>IT</option>
                        <option value="Дизайн" <?= $task['tag'] === 'Дизайн' ? 'selected' : '' ?>>Дизайн</option>
                        <option value="Маркетинг" <?= $task['tag'] === 'Маркетинг' ? 'selected' : '' ?>>Маркетинг</option>
                        <option value="Аналитика" <?= $task['tag'] === 'Аналитика' ? 'selected' : '' ?>>Аналитика</option>
                        <option value="Продажи" <?= $task['tag'] === 'Продажи' ? 'selected' : '' ?>>Продажи</option>
                        <option value="Копирайтинг" <?= $task['tag'] === 'Копирайтинг' ? 'selected' : '' ?>>Копирайтинг</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_id">Исполнитель:</label>
                    <select name="user_id" class="pattern_input" required>
                        <?php while ($user_row = $users_result->fetchArray(SQLITE3_ASSOC)): ?>
                            <option value="<?= $user_row['id'] ?>" <?= $user_row['id'] == $task['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user_row['username']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="progress">Прогресс (0-100%):</label>
                <input type="number" name="progress" min="0" max="100" class="pattern_input"
                       value="<?= htmlspecialchars($task['progress']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="deadline">Срок выполнения:</label>
                <input type="date" name="deadline" class="pattern_input"
                       value="<?= htmlspecialchars($task['deadline']) ?>" required 
                       <?= $user['role'] === 'executer' ? 'class="disabled-field" disabled' : '' ?>>
            </div>
            
            <!-- Чек-поинты -->
            <div class="checklist-container">
                <div class="checklist-title_edit">Чек-лист:</div>
                <?php 
                // Сбросим указатель результата, чтобы можно было снова его использовать
                $checklist_result->reset();
                while ($item = $checklist_result->fetchArray(SQLITE3_ASSOC)): ?>
                    <div class="checklist-item_edit">
                        <input type="hidden" name="checklist[<?= $item['id'] ?>]" value="0">
                        <input type="checkbox" name="checklist[<?= $item['id'] ?>]" id="checklist_<?= $item['id'] ?>"
                               value="1" <?= $item['is_checked'] ? 'checked' : '' ?>>
                        <label for="checklist_<?= $item['id'] ?>"><?= htmlspecialchars($item['item_text']) ?></label>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="button-group">
                <button type="submit" id="change_project" class="pattern_button_2">Сохранить</button>
                <a href="task_view.php?id=<?= $task_id ?>" id="edit_task_no">Отмена</a>
            </div>
        </form>
        
        <a href="project.php" class="back-link">← К проектам</a>
    </div>
</body>
</html>