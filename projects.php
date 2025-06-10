<?php
session_start();
require 'backend/data_fetch.php';
require 'backend/project_functions.php';
require 'backend/user_functions.php';


$db = new SQLite3('database.db');
$user_id = $_SESSION['user_id'];
$user = fetchUserData($db, $user_id);
$role = $user['role'];
$company_id = $user['company_id'];
$username = $user['username'];
$view_restrict = $user['view_restrict'];

$projects = fetchProjects($db, $user_id);
$tasks = fetchTasks($db);
$users_okei = fetchUsers($db, $user_id);

if ($role === 'manager') {
    $members_result = fetchCompanyMembers($db, $company_id);
    $users_result = fetchAllUsers($db);
}

$users_manager = fetchManagerData($db, $company_id);

$error_create_task = handleCreateTask($db, $project_id, $tasks_name, $task_description, $importance, $progress, $deadline, $user_id_task, $checklist_items = [], $tag = null);
$error_create_project = handleProjectCreation($db, $user_id);
$error_add_user = handleUserRegistration($db, $company_id);
$error_edit_project = handleEditProject($db, $project_id, $user_id);



?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление проектами</title>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <script src="js/main.js" defer></script>
    <script src="js/filters.js" defer></script>
    <script src="js/modals.js" defer></script>
    <script src="js/ajax.js" defer></script>
</head>
<body>

    <!-- ===================== БЛОК ДЛЯ МЕНЕДЖЕРОВ ===================== -->
    <?php if ($role === 'manager' && $username !== 'admin'): ?>
        <div class="block_manager_company">
            <div class="block_create_project">
                <p class="pattern_heading">Создать проект</p>
                <form method="POST">
                    <label>Название:</label>
                    <input type="text" name="project_name" class="pattern_input" placeholder="до 20 символов" required maxlength="20"><br>
                    <button type="submit" name="create_project" id="create_project" class="pattern_button_2">Создать</button>
                </form>
                <?php if (isset($error_create_project)) echo "<p style='color:red; text-align:center; margin-top:-100px;'>$error_create_project</p>"; ?>
            </div>
            
            <div id="register_executer">
                <div id="window_register_executer" class="pattern_modal">
                    <a href="#"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
                    <p id="name_register_executer" class="pattern_heading">Добавить сотрудника</p>
                    <form method="POST">
                        <label>username:</label>
                        <input type="text" name="new_username" id="register_username_executer" class="pattern_input" placeholder="до 10 символов" required maxlength="10"><br>
                        <label>пароль:</label>
                        <input type="password" name="password" id="register_password_executer" class="pattern_input" required maxlength="20"><br>
                        <label>повторите пароль:</label>
                        <input type="password" name="confirm_password" id="register_confirm_password_executer" class="pattern_input" required maxlength="20"><br>
                        <input type="submit" name="add_user" value="Добавить" id="change_project" class="pattern_button_2">
                    </form>
                    <?php if (isset($error_add_user)) echo "<p style='color:red; text-align:center; margin-top:-88px;'>$error_add_user</p>"; ?>
                </div>
            </div>
            
            <div id="delete_executer">
                <div id="window_delete_executer" class="pattern_modal">
                    <a href="#"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
                    <p id="name_delete_executer" class="pattern_heading">Удалить сотрудника</p>
                    <ul>
                        <?php 
                        $members_result->reset();
                        while ($member = $members_result->fetchArray(SQLITE3_ASSOC)): 
                            if ($member['role'] === 'executer'): ?>
                            <li>
                                <p><?php echo htmlspecialchars($member['username']); ?></p>
<form method="POST" action="user_project_actions.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя \'<?= addslashes($member['username']) ?>\'?')">
    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
    <input type="hidden" name="delete_user" value="1">
    <button type="submit" id="delete_user" class="pattern_button_3">Удалить</button>
</form>
                            </li>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
            
            <div class="block_company_list">
                <p id="name_company_executers" class="pattern_heading">Сотрудники вашей компании</p>
                <ul>
                    <?php 
                    $members_result->reset();
                    while ($member = $members_result->fetchArray(SQLITE3_ASSOC)): ?>
                        <li>
                            <a href="#" onclick="document.getElementById('user_info_<?php echo $member['id']; ?>').style.display='block'"><?php echo htmlspecialchars($member['username']); ?></a>
                            <?php echo htmlspecialchars(' - ' . translateRole($member['role'])); ?>
                            
                            <?php
                            $stmt = $db->prepare("SELECT username, first_name, last_name, patronymic, email FROM Users WHERE id = :id");
                            $stmt->bindParam(':id', $member['id'], SQLITE3_INTEGER);
                            $user_info = $stmt->execute();
                            $user_data = $user_info->fetchArray(SQLITE3_ASSOC);
                            ?>
                            
                            <div id="user_info_<?php echo $member['id']; ?>" class="modal" style="display:none;">
                                <div id="window_user_info" class="pattern_modal">
                                    <a onclick="document.getElementById('user_info_<?php echo $member['id']; ?>').style.display='none'"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
                                    <p id="name_user_info" class="pattern_heading">Профиль <?php echo htmlspecialchars($member['username']); ?></p>
                                    <p id="window_user_info_p"><strong>Фамилия:</strong> <?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?></p>
                                    <p id="window_user_info_p"><strong>Имя:</strong> <?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?></p>
                                    <p id="window_user_info_p"><strong>Отчество:</strong> <?php echo htmlspecialchars($user_data['patronymic'] ?? ''); ?></p>
                                    <p id="window_user_info_p"><strong>Почта:</strong> <?php echo htmlspecialchars($user_data['email'] ?? ''); ?></p>
                                </div>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <div class="buttons_company">
                    <button onclick="window.location.href='#register_executer'" id="button_register_executer" class="pattern_button_1">Добавить сотрудника</button>
                    <button onclick="window.location.href='#delete_executer'" id="button_delete_executer" class="pattern_button_3">Удалить сотрудника</button>
                </div>
            </div>
        </div>

        <div class="block_projects_company">
            <p id="name_projects_list" class="pattern_heading">Проекты</p>
            <div class="container_project">
                <?php 
                $all_projects = [];
                while ($project = $projects->fetchArray(SQLITE3_ASSOC)) {
                    $all_projects[] = $project;
                }
                
                $users_for_filters = [];
                $users_okei->reset();
                while ($user = $users_okei->fetchArray(SQLITE3_ASSOC)) {
                    $users_for_filters[$user['id']] = $user['username'];
                }
                
                foreach ($all_projects as $project): ?>
                    <div class="block_project">
                        <li>
                            <?php $projectID = $project['id']; ?>
                            <p id="project_name_<?php echo $projectID; ?>" class="project_name"><?php echo htmlspecialchars($project['name']); ?></p>
                            <hr>

                            <div class="filters-container" id="filters_<?php echo $projectID; ?>">
                                <div class="filter-header" onclick="toggleFilters(<?php echo $projectID; ?>)">
                                    <p class="filter-label">Фильтры: <span class="arrow-icon">▼</span></p>
                                </div>
                                <div class="filter-content" style="display: none;">
                                    <div class="filter-group">
                                        <select class="filter-select" id="importance_filter_<?php echo $projectID; ?>" onchange="filterTasks(<?php echo $projectID; ?>)">
                                            <option value="">Важность</option>
                                            <option value="high">Высокая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="low">Низкая</option>
                                        </select>
                                        
                                        <select class="filter-select_username" id="user_filter_<?php echo $projectID; ?>" onchange="filterTasks(<?php echo $projectID; ?>)">
                                            <option value="">Исполнитель</option>
                                            <?php foreach ($users_for_filters as $id => $username): ?>
                                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($username); ?></option>
                                            <?php endforeach; ?>
                                        </select><br>
                                        
                                        <button class="filter-button reset-filters" onclick="resetFilters(<?php echo $projectID; ?>)">Сбросить</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="tasks_container_<?php echo $projectID; ?>">
                                <?php
                                foreach ($tasks as $task) {
                                    if ($task['project_id'] === $projectID && $task['progress'] !== 100) {
                                        displayTask($task, $db, $users_okei, false, true);
                                    }
                                }
                                ?>
                            </div>
                            <a class="create_task" href="task_create.php?project_id=<?php echo $projectID; ?>">Добавить задачу</a>
                            <button onclick="showEditProjectModal(<?php echo $projectID; ?>)" id="change_project" class="pattern_button_2">Изменить</button>
                            
                            <div id="edit_project_<?php echo $projectID; ?>" class="modal" style="display:none;">
                                <div id="window_edit_project" class="pattern_modal">
                                    <a href="#" onclick="hideEditProjectModal(<?php echo $projectID; ?>)"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
                                    <p id="name_edit_project" class="pattern_heading">Редактировать проект</p>
                                    <form method="POST" id="edit_project_form_<?php echo $projectID; ?>">
                                        <input type="hidden" name="project_id" value="<?php echo $projectID; ?>">
                                        <input type="text" name="project_name" id="projects_name_<?php echo $projectID; ?>" value="<?php echo htmlspecialchars($project['name']); ?>" class="pattern_input" placeholder="до 20 символов" required maxlength="20"><br>
                                        <button type="submit" id="change_project" class="pattern_button_2">Сохранить</button>
                                    </form>
<form method="POST" action="user_project_actions.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить проект \'<?= addslashes($project['name']) ?>\'?')">
    <input type="hidden" name="project_id" value="<?php echo $projectID; ?>">
    <input type="hidden" name="delete_project" value="1">
    <button type="submit" id="delete_project">Удалить проект</button>
</form>
                                </div>
                            </div>
                        </li>
                    </div>
                <?php endforeach; ?>
                
                <div class="block_project">
                    <p class="project_name">Выполненные</p>
                    <hr>
                    <?php
                    foreach ($tasks as $task) {
                        if ($task['progress'] === 100) {
                            displayTask($task, $db, $users_okei, true, true);
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ===================== БЛОК ДЛЯ АДМИНИСТРАТОРА ===================== -->
    <?php if ($username === 'admin'): ?>
        <div class="block_company_list_admin">
            <p id="name_company_admin_list" class="pattern_heading">Пользователи в системе</p>
            <ul>
                <?php while ($users = $users_result->fetchArray(SQLITE3_ASSOC)): ?>
                    <li>
                        <a href="user_info.php?id=<?php echo $users['id']; ?>"><?php echo htmlspecialchars($users['username']); ?></a>
                        <?php echo htmlspecialchars(' - ' . translateRole($users['role'])); ?>
                        <?php if ($users['username'] !== 'admin'): ?>
<form method="POST" action="user_project_actions.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите удалить пользователя \'<?= addslashes($users['username']) ?>\'?')">
    <input type="hidden" name="user_id" value="<?php echo $users['id']; ?>">
    <input type="hidden" name="delete_user" value="1">
    <button type="submit" name="delete_user" id="delete_user_admin">Удалить</button>
</form>
                        <?php endif; ?>    
                        <?php if ($users['role'] === 'manager' && $users['username'] !== 'admin'): ?>
<form method="POST" action="user_project_actions.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите понизить \'<?= addslashes($users['username']) ?>\'?')">
    <input type="hidden" name="user_id" value="<?php echo $users['id']; ?>">
    <input type="hidden" name="downgrade_user" value="1">
    <button type="submit" name="downgrade_user" id="downgrade_upgrade_user">&#8595;</button>
</form>
                        <?php endif; ?>       
                        <?php if ($users['role'] === 'executer'): ?>
<form method="POST" action="user_project_actions.php" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите повысить \'<?= addslashes($users['username']) ?>\'?')">
    <input type="hidden" name="user_id" value="<?php echo $users['id']; ?>">
    <input type="hidden" name="update_user" value="1">
    <button type="submit" name="update_user" id="downgrade_upgrade_user">&#8593;</button>
</form>
                        <?php endif; ?>                     
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php endif; ?>

    <!-- ===================== БЛОК ДЛЯ ИСПОЛНИТЕЛЕЙ ===================== -->
    <?php if ($role === 'executer'): ?>
        <div class="block_projects_company">
            <p id="name_projects_list" class="pattern_heading">Проекты</p>
            <div class="container_project">
                <?php 
                $projects->reset();
                while ($project = $projects->fetchArray(SQLITE3_ASSOC)): 
                
                $has_tasks_in_project = false;
                if ($view_restrict === 'yes') {
                    $check_stmt = $db->prepare("SELECT COUNT(*) FROM Tasks WHERE project_id = :project_id AND user_id = :user_id AND progress < 100");
                    $check_stmt->bindParam(':project_id', $project['id'], SQLITE3_INTEGER);
                    $check_stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
                    $result = $check_stmt->execute();
                    $count = $result->fetchArray(SQLITE3_NUM)[0];
                    $has_tasks_in_project = ($count > 0);
                }
                
                if ($view_restrict === 'no' || $has_tasks_in_project): 
                
                $users_for_filters = [];
                $users_okei->reset();
                while ($user = $users_okei->fetchArray(SQLITE3_ASSOC)) {
                    $users_for_filters[$user['id']] = $user['username'];
                }?>
                    <div class="block_project">
                        <li>
                            <?php $projectID = $project['id']; ?>
                            <p class="project_name"><?php echo htmlspecialchars($project['name']); ?></p>
                            <hr>

                            <div class="filters-container" id="filters_<?php echo $projectID; ?>">
                                <div class="filter-header" onclick="toggleFilters(<?php echo $projectID; ?>)">
                                    <p class="filter-label">Фильтры: <span class="arrow-icon">▼</span></p>
                                </div>
                                <div class="filter-content" style="display: none;">
                                    <div class="filter-group">
                                        <select class="filter-select" id="importance_filter_<?php echo $projectID; ?>" onchange="filterTasks(<?php echo $projectID; ?>)">
                                            <option value="">Важность</option>
                                            <option value="high">Высокая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="low">Низкая</option>
                                        </select>
                                        
                                        <select class="filter-select_username" id="user_filter_<?php echo $projectID; ?>" onchange="filterTasks(<?php echo $projectID; ?>)">
                                            <option value="">Исполнитель</option>
                                            <?php foreach ($users_for_filters as $id => $username): ?>
                                                <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($username); ?></option>
                                            <?php endforeach; ?>
                                        </select><br>
                                        
                                        <button class="filter-button reset-filters" onclick="resetFilters(<?php echo $projectID; ?>)">Сбросить</button>
                                    </div>
                                </div>
                            </div>

                            <div id="tasks_container_<?php echo $projectID; ?>">
                            <?php
                            foreach ($tasks as $task) {
                                if ($task['project_id'] === $projectID && $task['progress'] !== 100) {
                                    if ($users_manager['view_restrict'] === 'no' || 
                                        ($users_manager['view_restrict'] === 'yes' && $task['user_id'] == $user_id)) {
                                        displayTask($task, $db, $users_okei, false, false);
                                    }
                                }
                            }
                            ?>
                            </div>
                        </li>
                    </div>
                <?php endif; ?>
                <?php endwhile; ?>
                
                <div class="block_project">
                    <p class="project_name">Выполненные</p>
                    <hr>
                    <?php
                    foreach ($tasks as $task) {
                        if ($task['progress'] === 100) {
                            if ($users_manager['view_restrict'] === 'no' || 
                                ($users_manager['view_restrict'] === 'yes' && $task['user_id'] == $user_id)) {
                                displayTask($task, $db, $users_okei, true, false);
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>