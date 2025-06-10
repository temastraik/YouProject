<?php
require_once 'functions.php';

$error_add_note = '';
$edit_note = null;
$order = 'DESC';

// Обработка добавления заметки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $title = trim($_POST['note_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title)) {
        $checkStmt = $db->prepare('SELECT COUNT(*) FROM Notes WHERE note_name = :note_name AND user_id = :user_id');
        $checkStmt->bindValue(':note_name', $title, SQLITE3_TEXT);
        $checkStmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $result = $checkStmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0] ?? 0;
        
        if ($count > 0) {
            $error_add_note = "Заметка с таким заголовком уже существует!";
        } else {
            $stmt = $db->prepare('INSERT INTO Notes (note_name, description, user_id) VALUES (:note_name, :description, :user_id)');
            $stmt->bindValue(':note_name', $title, SQLITE3_TEXT);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                header('Location: '.$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_add_note = "Ошибка при добавлении заметки!";
            }
        }
    } else {
        $error_add_note = "Заголовок заметки не может быть пустым!";
    }
}

// Обработка обновления заметки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_note'])) {
    $id = $_POST['id'] ?? 0;
    $title = trim($_POST['note_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($title) && $id > 0) {
        $checkStmt = $db->prepare('SELECT COUNT(*) FROM Notes WHERE note_name = :note_name AND user_id = :user_id AND id != :id');
        $checkStmt->bindValue(':note_name', $title, SQLITE3_TEXT);
        $checkStmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $checkStmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $checkStmt->execute();
        $count = $result->fetchArray(SQLITE3_NUM)[0] ?? 0;
        
        if ($count > 0) {
            $error_add_note = "Другая заметка с таким заголовком уже существует!";
        } else {
            $stmt = $db->prepare('UPDATE Notes SET note_name = :note_name, description = :description WHERE id = :id AND user_id = :user_id');
            $stmt->bindValue(':note_name', $title, SQLITE3_TEXT);
            $stmt->bindValue(':description', $description, SQLITE3_TEXT);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                header('Location: '.$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_add_note = "Ошибка при обновлении заметки!";
            }
        }
    } else {
        $error_add_note = "Заголовок заметки не может быть пустым и ID должен быть корректным!";
    }
}

// Обработка удаления заметки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_note'])) {
    $id = $_POST['id'] ?? 0;
    
    if ($id > 0) {
        $stmt = $db->prepare('DELETE FROM Notes WHERE id = :id AND user_id = :user_id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        header('Location: '.$_SERVER['PHP_SELF']);
        exit();
    }
}

// Сортировка заметок
if (isset($_GET['filter'])) {
    $order = ($_GET['filter'] === 'oldest') ? 'ASC' : 'DESC';
}

// Получение заметок
$result = $db->query("SELECT * FROM Notes WHERE user_id = ".$_SESSION['user_id']." ORDER BY created_at $order");
$notes = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $notes[] = $row;
}

// Получение заметки для редактирования
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $result = $db->query("SELECT * FROM Notes WHERE id = $id AND user_id = ".$_SESSION['user_id']);
        $edit_note = $result->fetchArray(SQLITE3_ASSOC);
    }
}
?>