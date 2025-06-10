<?php 
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
include('header.php'); 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Проекты</title>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Контент для авторизованных пользователей -->
        <?php 
        session_start();
        $current_page = basename($_SERVER['PHP_SELF']);
        include('projects.php'); 
        ?>
    <?php else: ?>
        <!-- Контент для неавторизованных пользователей -->
        <div class="block_0">
            <p id="block_1_heading">Проекты</p>
            <p id="block_1_text">Система <b>для</b> чёткого <b>контроля</b>: группируйте задачи, распределяйте их между командой, выделяйте важное и отслеживайте прогресс</p>
        </div>
        <div class="block_10">
            <p id="block_1_heading">Пример проекта</p>
            <img src="Image/primer_1.png" alt="Пример проектов">
        </div>
        <div class="block_1">
            <p id="block_1_heading">Задачи</p>
            <p id="block_1_text">Каждая задача содержит <b>все необходимое</b>: название, ответственного, прогресс выполнения, уровень важности и детальное описание</p>
        </div>
        <div class="block_1">
            <p id="block_1_heading">Пример задач</p>
            <div class="container_tasks">
                <img src="Image/primer_2.png" alt="Пример описания задачи">
                <img src="Image/primer_3.png" alt="Пример описания задачи">
            </div>
        </div>
        <div class="block_1">
            <p id="block_1_heading">Компания</p>
            <p id="block_1_text">Гибкое распределение: <b>добавляйте</b> или <b>удаляйте</b> исполнителей моментально. Чёткий контроль — эффективная работа команды</p>
        </div>
        <div class="block_1">
            <p id="block_1_heading">Пример компании</p>
            <img src="Image/primer_8.png" alt="Пример компании">
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