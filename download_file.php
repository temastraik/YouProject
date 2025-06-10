<?php

$task_id = $_GET['task_id'] ?? 0;

// Получение информации о задаче и файле
$stmt = $db->prepare("SELECT file_path FROM Tasks WHERE id = :task_id");
$stmt->bindParam(':task_id', $task_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$task = $result->fetchArray(SQLITE3_ASSOC);

if (!$task || empty($task['file_path']) || !file_exists($task['file_path'])) {
    die("Файл не найден");
}

$file_path = $task['file_path'];
$file_name = basename($file_path);
// Удаляем уникальный префикс для скачивания с оригинальным именем
$original_name = preg_replace('/^[a-z0-9]+_/', '', $file_name);

// Определяем MIME-тип файла
$mime_types = [
    'txt' => 'text/plain',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'pdf' => 'application/pdf'
];
$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$mime_type = $mime_types[$extension] ?? 'application/octet-stream';

// Отправляем файл для скачивания
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $original_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>