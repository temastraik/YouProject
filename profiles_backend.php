<?php
session_start();
$db = new SQLite3('database.db');

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$user_info = $db->query("SELECT username, first_name, last_name, patronymic, email, company_id, id, role, view_restrict FROM Users WHERE id = $user_id");
$user = $user_info->fetchArray(SQLITE3_ASSOC);
$companyID = $user['company_id'];
$company_info = $db->query("SELECT name FROM Company WHERE id = $companyID");
$company = $company_info->fetchArray(SQLITE3_ASSOC);
$company_name = $company['name'];

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username']);
    $newFirstName = trim($_POST['first_name']);
    $newLastName = trim($_POST['last_name']);
    $newPatronymic = trim($_POST['patronymic']);
    $newEmail = trim($_POST['email']);
    $view_restrict = trim($_POST['view_restrict']);
    
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        // Обновление данных в БД
        $stmt = $db->prepare("UPDATE Users SET username = :username, view_restrict = :view_restrict, first_name = :first_name, last_name = :last_name, patronymic = :patronymic, email = :email WHERE id = :id");
        $stmt->bindValue(':username', $newUsername, SQLITE3_TEXT);
        $stmt->bindValue(':first_name', $newFirstName, SQLITE3_TEXT);
        $stmt->bindValue(':last_name', $newLastName, SQLITE3_TEXT);
        $stmt->bindValue(':patronymic', $newPatronymic, SQLITE3_TEXT);
        $stmt->bindValue(':email', $newEmail, SQLITE3_TEXT);
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':view_restrict', $view_restrict, SQLITE3_TEXT);
    
        // Обновление данных на странице
        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $_SESSION['first_name'] = $newFirstName;
            $_SESSION['last_name'] = $newLastName;
            $_SESSION['patronymic'] = $newPatronymic;
            $_SESSION['email'] = $newEmail;
            $_SESSION['view_restrict'] = $view_restrict;
            // Перенос на главную страницу
            header('Location: profile.php');
            exit();
        } else {
            $error_message = "Ошибка при обновлении данных: " . $db->lastErrorMsg();
        }
    } else {
        $error_message = "Введите корректную почту";
    }
}
?>