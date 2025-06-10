<?php

function handleCreateTask($db, $project_id, $tasks_name, $task_description, $importance, $progress, $deadline, $user_id_task, $checklist_items = [], $tag = null) {
    $error_create_task = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tasks_name'])) {
        $project_id = $_POST['project_id'];
        $tasks_name = trim($_POST['tasks_name']);
        $task_description = trim($_POST['task_description']);
        $importance = $_POST['importance'];
        $progress = $_POST['progress'];
        $deadline = $_POST['deadline'];
        $user_id_task = $_POST['user_id'];
        $tag = !empty($_POST['tag']) ? $_POST['tag'] : null;
        
        // Обработка загруженного файла
        $file_path = null;
        if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['task_file'];
            
            // Проверка расширения файла
            $allowed_extensions = ['txt', 'docx', 'pdf'];
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_create_task = "Недопустимый формат файла. Разрешены только txt, docx, pdf.";
                return $error_create_task;
            }
            
            // Проверка размера файла (до 1MB)
            if ($file['size'] > 1048576) {
                $error_create_task = "Файл слишком большой. Максимальный размер - 1MB.";
                return $error_create_task;
            }
            
            // Создаем папку для файлов, если ее нет
            $upload_dir = 'user_files';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Генерируем уникальное имя файла
            $file_name = uniqid() . '_' . basename($file['name']);
            $file_path = $upload_dir . '/' . $file_name;
            
            // Перемещаем файл в целевую директорию
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                $error_create_task = "Ошибка при загрузке файла.";
                return $error_create_task;
            }
        }
        
        // Получаем элементы чек-листа
        $checklist_items = [];
        for ($i = 1; $i <= 5; $i++) {
            if (!empty(trim($_POST['checklist_item_' . $i] ?? ''))) {
                $checklist_items[] = $_POST['checklist_item_' . $i];
            }
        }
        
        // Проверка на уникальность названия задачи в рамках проекта
        $check_stmt = $db->prepare("SELECT COUNT(*) FROM Tasks WHERE name = :name AND project_id = :project_id");
        $check_stmt->bindParam(':name', $tasks_name, SQLITE3_TEXT);
        $check_stmt->bindParam(':project_id', $project_id, SQLITE3_INTEGER);
        $result = $check_stmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0];
        
        if ($count > 0) {
            $error_create_task = "Задача с таким названием уже существует в этом проекте";
        } else {
            // Создание новой задачи с учетом файла
            $stmt = $db->prepare("INSERT INTO Tasks (name, description, importance, user_id, project_id, progress, deadline, tag, file_path) 
                                  VALUES (:name, :description, :importance, :user_id, :project_id, :progress, :deadline, :tag, :file_path)");
            $stmt->bindParam(':name', $tasks_name, SQLITE3_TEXT);
            $stmt->bindParam(':description', $task_description, SQLITE3_TEXT);
            $stmt->bindParam(':importance', $importance, SQLITE3_TEXT);
            $stmt->bindParam(':user_id', $user_id_task, SQLITE3_INTEGER);
            $stmt->bindParam(':project_id', $project_id, SQLITE3_INTEGER);
            $stmt->bindParam(':progress', $progress, SQLITE3_INTEGER);
            $stmt->bindParam(':deadline', $deadline, SQLITE3_TEXT);
            $stmt->bindParam(':tag', $tag, SQLITE3_TEXT);
            $stmt->bindParam(':file_path', $file_path, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $task_id = $db->lastInsertRowID();
                
                // Добавляем элементы чек-листа, если они есть
                if (!empty($checklist_items)) {
                    foreach ($checklist_items as $item) {
                        if (!empty(trim($item))) {
                            $stmt = $db->prepare("INSERT INTO Checklists (task_id, item_text) VALUES (:task_id, :item_text)");
                            $stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
                            $stmt->bindParam(':item_text', trim($item), SQLITE3_TEXT);
                            $stmt->execute();
                        }
                    }
                }
                
                header("Location: project.php");
                exit();
            } else {
                // Если возникла ошибка при сохранении задачи, удаляем загруженный файл
                if ($file_path && file_exists($file_path)) {
                    unlink($file_path);
                }
                $error_create_task = "Ошибка при создании задачи: " . $db->lastErrorMsg();
            }
        }
    }
    
    return $error_create_task;
}

function handleProjectCreation($db, $user_id) {
    $error_create_project = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
        $project_name = trim($_POST['project_name']);

        // Проверка на существование проекта с таким же названием в текущей компании
        $check_stmt = $db->prepare("SELECT COUNT(*) FROM Projects 
                                  WHERE name = :name 
                                  AND company_id = (SELECT company_id FROM Users WHERE id = :user_id)");
        $check_stmt->bindParam(':name', $project_name, SQLITE3_TEXT);
        $check_stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $check_stmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0];
        
        if ($count > 0) {
            $error_create_project = "Проект с таким названием уже существует";
        } else {
            // Создание нового проекта
            $stmt = $db->prepare("INSERT INTO Projects (name, company_id) VALUES (:name, (SELECT company_id FROM Users WHERE id = :user_id))");
            $stmt->bindParam(':name', $project_name, SQLITE3_TEXT);
            $stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
            
            if (!$stmt->execute()) {
                $error_create_project = "Ошибка при создании проекта: " . $db->lastErrorMsg();
            } else {
                // Перенаправление после успешного создания
                header("Location: project.php");
                exit();
            }
        }
    }
    
    return $error_create_project;
}

function handleUserRegistration($db, $company_id) {
    $error_add_user = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $new_username = trim($_POST['new_username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Сброс предыдущей ошибки
        $error_add_user = null;
    
        if ($password !== $confirm_password) {
            $error_add_user = "Пароли не совпадают";
        } elseif (strlen($new_username) > 10) {
            $error_add_user = "Username должен быть не больше 10 символов";
        } else {
            // Проверка существования пользователя
            $stmt = $db->prepare("SELECT * FROM Users WHERE username = :username");
            $stmt->bindParam(':username', $new_username, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if ($result->fetchArray(SQLITE3_ASSOC)) {
                $error_add_user = "Пользователь с таким именем уже существует.";
            } else {
                // Создание нового пользователя
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO Users (username, password, role, company_id) 
                                VALUES (:username, :password, 'executer', :company_id)");
                $stmt->bindParam(':username', $new_username, SQLITE3_TEXT);
                $stmt->bindParam(':password', $password_hash, SQLITE3_TEXT);
                $stmt->bindParam(':company_id', $company_id, SQLITE3_INTEGER);

                if (!$stmt->execute()) {
                    $error_add_user = "Ошибка при создании пользователя: " . $db->lastErrorMsg();
                } else {
                    // Перенаправление после успешного добавления
                    header("Location: project.php");
                    exit();
                }
            }
        }
    }
    
    return $error_add_user;
}

function handleEditProject($db, $project_id, $user_id) {
    $error_edit_project = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id']) && isset($_POST['project_name'])) {
        $project_id = $_POST['project_id'];
        $new_name = trim($_POST['project_name']);
        
        $check_stmt = $db->prepare("SELECT COUNT(*) FROM Projects 
                                  WHERE name = :name 
                                  AND company_id = (SELECT company_id FROM Users WHERE id = :user_id)");
        $check_stmt->bindParam(':name', $new_name, SQLITE3_TEXT);
        $check_stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $check_stmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0];
        
        if ($count > 0) {
            $error_edit_project = "Проект с таким названием уже существует";
        } else {
            $stmt = $db->prepare("UPDATE Projects SET name = :name WHERE id = :id");
            $stmt->bindParam(':name', $new_name, SQLITE3_TEXT);
            $stmt->bindParam(':id', $project_id, SQLITE3_INTEGER);
            $stmt->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['new_name' => $new_name]);
            exit;
        }
    }

    return $error_edit_project;
}

?>