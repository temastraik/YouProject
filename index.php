<?php 
session_start();
$db = new SQLite3('database.db');

$current_page = basename($_SERVER['PHP_SELF']);
include('header.php'); 

// Обработка отправки отзыва
if (isset($_SESSION['user_id']) && isset($_POST['review_text']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_text = trim($_POST['review_text']);
    if (!empty($review_text)) {
        $stmt = $db->prepare("INSERT INTO Reviews (review_text, user_id) VALUES (:review_text, :user_id)");
        $stmt->bindValue(':review_text', $review_text, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->execute();
        
        // Перенаправление на эту же страницу
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>О нас</title>
</head>
<body>
    <div class="block_2">
        <div class="block_2_2">
            <div class="block_2_1">
                <p id="block_2_header"><b>YouProject</b> - система управления задачами</p>
                <p id="block_2_text">удобный, интуитивно понятный и функциональный инструмент, который станет незаменимым помощником в повседневной работе.</p>
            </div>
            <img src=Image/1.png>
        </div>
    </div>
  
    <div class="block_3">
        <p id="block_3_header" class="pattern_heading">Мы предоставляем:</p>
        <div class="block_3_1">
            <p id="block_3_header_1">1. Упрощение планирования</p>
            <p id="block_3_text_1">Создание и сортировка задач по приоритетам помогает организовать свои действия и избегать перегрузки</p>
            <p id="block_3_header_2">2. Увеличение продуктивности</p>
            <p id="block_3_text_2">Пользователи более эффективно управляют временем, что позволяет сосредоточиться на выполнении задач и снижает риск пропуска дедлайнов</p>
            <p id="block_3_header_1">3. Совместная работа </p>
            <p id="block_3_text_1">Команда может делиться задачами, комментировать их и отслеживать прогресс</p>
            <p id="block_3_header_3">4. Автоматизация процессов</p>
            <p id="block_3_text_3">Наша система позволяет автоматизировать рутинные задачи и упрощает рабочий процесс</p>
        </div>
    </div>
  
    <div class="block_4">
        <p id="block_4_header"><b>YouProject</b> — ваш <b>идеальный инструмент</b> для управления задачами</p>
        <div class="block_4_1">
            <p id="block_4_text">Платформа создана для каждого, кто <b>ценит порядок и эффективность</b>: от студентов и фрилансеров, до малых и крупных компаний</p>
            <p id="block_4_text_2"><b>Как взаимодействовать</b> с платформой?</p>
            <p id="block_4_text_2">1. <b>Менеджер проходит регистрацию</b> и создает компанию → 2. Менеджер добавляет сотрудников компании с ролью исполнителя → 3. Менеджер создает <b>проекты и задачи</b>, к которым привязан конкретный исполнитель → 4. Исполнитель <b>редактирует прогресс выполнения</b>, а менеджер отслеживает успех → 5. Когда прогресс выполнения достигает 100%, задача переносится в <b>раздел "Выполненные"</b></p>
            <img src=Image/2.png>
        </div>
    </div>

    <!-- Отзывы -->
    <div class="reviews-section">
        <p id="reviews_section_header" class="pattern_heading">Отзывы о системе</p>
        
        <?php
        // Получение всех отзывов
        $reviews = $db->query("
            SELECT Reviews.review_text, Users.username 
            FROM Reviews 
            JOIN Users ON Reviews.user_id = Users.id 
            ORDER BY Reviews.id DESC
        ");
        ?>
        
        <div class="reviews-list">
            <?php while ($review = $reviews->fetchArray()): ?>
                <div class="review-item">
                    <div class="review-user">
                        <p>
                            <?= htmlspecialchars($review['username']) ?>
                        </p>
                    </div>
                    <div class="review-text">
                        «<?= htmlspecialchars($review['review_text']) ?>»
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <p class="pattern_heading">Оставить отзыв</p>
                <form method="POST">
                    <input name="review_text" placeholder="Ваш отзыв о системе (до 200 символов)" required maxlength="200"></input>
                    <button type="submit" id="change_project" class="pattern_button_2">Отправить</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    include('footer.php'); 
    ?>
</body>
</html>