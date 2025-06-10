<?php // user_project_actions.php - Объединенный файл для обработки действий с пользователями и проектами
session_start();
require_once 'backend/data_fetch.php';

$db = new SQLite3('database.db');

function deleteUser($db, $user_id) {
    $stmt = $db->prepare("DELETE FROM Users WHERE id = :user_id");
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function deleteProject($db, $project_id) {
    $stmt = $db->prepare("DELETE FROM Projects WHERE id = :project_id");
    $stmt->bindValue(':project_id', $project_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function deleteTask($db, $task_id) {
    $stmt = $db->prepare("DELETE FROM Tasks WHERE id = :task_id");
    $stmt->bindValue(':task_id', $task_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function changeUserRole($db, $user_id, $new_role) {
    $stmt = $db->prepare("UPDATE Users SET role = :role WHERE id = :user_id");
    $stmt->bindValue(':role', $new_role, SQLITE3_TEXT);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        deleteUser($db, $user_id);
    } 
    elseif (isset($_POST['project_id']) && isset($_POST['delete_project'])) {
        $project_id = intval($_POST['project_id']);
        deleteProject($db, $project_id);
    }
    elseif (isset($_POST['task_id'])) {
        $task_id = intval($_POST['task_id']);
        deleteTask($db, $task_id);
    }
    elseif (isset($_POST['downgrade_user'])) {
        $user_id = intval($_POST['user_id']);
        changeUserRole($db, $user_id, 'executer');
    }
    elseif (isset($_POST['update_user'])) {
        $user_id = intval($_POST['user_id']);
        changeUserRole($db, $user_id, 'manager');
    }
}

header('Location: project.php');
?>