<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id']) && isset($_POST['project_name'])) {
    $db = new SQLite3('database.db');
    $project_id = $_POST['project_id'];
    $new_name = trim($_POST['project_name']);
    
    $stmt = $db->prepare("UPDATE Projects SET name = :name WHERE id = :id");
    $stmt->bindParam(':name', $new_name, SQLITE3_TEXT);
    $stmt->bindParam(':id', $project_id, SQLITE3_INTEGER);
    $stmt->execute();
    
    header('Content-Type: application/json');
    echo json_encode(['new_name' => $new_name]);
    exit;
}
?>