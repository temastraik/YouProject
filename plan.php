<?php 
session_start();
$db = new SQLite3('database.db');


$current_page = basename($_SERVER['PHP_SELF']);
include('header.php'); 
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Планировщик</title>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Контент для авторизованных пользователей -->
        <?php 
        session_start();
        $current_page = basename($_SERVER['PHP_SELF']);
        include('plans.php'); 
        ?>
    <?php else: ?>
        <!-- Контент для неавторизованных пользователей -->
        <div class="block_0">
            <p id="block_1_heading">Календарь-планировщик</p>
            <p id="block_1_text">Ваш идеальный помощник: календарь с <b>событиями</b> и <b>задачами</b> для чёткого расписания</p>
        </div>
        
        <div class="block_1">
            <p id="block_1_heading">Пример календаря-планировщика</p>
            <img src="Image/primer_4.png" alt="Пример календаря-планировщика">
        </div>

        <div class="block_1">
            <p id="block_1_heading">Заметки</p>
            <p id="block_1_text">Храните идеи, списки дел или важные мысли в удобном формате. <b>Ничего лишнего</b> — только заголовок и текст</p>
        </div>
        
        <div class="block_10">
            <p id="block_1_heading">Пример заметок</p>
            <img src="Image/primer_9.png" alt="Пример заметок">
        </div>

        <div class="block_1">
            <p id="block_1_heading">Хотите также?</p>
            <p id="block_1_text">Тогда <a href="#login">войдите</a> или <a href="#register">зарегистрируйте</a> новый аккаунт</p>
        </div>
    <?php endif; ?>
    <?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    include('footer.php'); 
    ?>
</body>
</html>