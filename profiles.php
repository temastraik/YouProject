<?php 
require_once 'profiles_backend.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
</head>
<body>
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="block_9">
            <p id="block_5_heading" class="pattern_heading">Ваш профиль</p>
            <div class="block_6">
                <div class="block_7">
                    <p id="block_6_type">Фамилия:</p>
                    <input type="text" id="block_6_text" name="last_name" placeholder="до 15 символов" maxlength="15" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                </div>
                <div class="block_7">
                    <p id="block_6_type">Почта:</p>
                    <input type="text" id="block_6_text" name="email" placeholder="до 25 символов" maxlength="25" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="block_7">
                    <p id="block_6_type">Имя:</p>
                    <input type="text" id="block_6_text" name="first_name" placeholder="до 15 символов" maxlength="15" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                </div>
                <div class="block_7">
                    <p id="block_6_type">Компания:</p>
                    <input type="text" id="block_6_text" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" disabled>
                </div>
                <div class="block_7">
                    <p id="block_6_type">Отчество:</p>
                    <input type="text" id="block_6_text" name="patronymic" placeholder="до 15 символов" maxlength="15" value="<?php echo htmlspecialchars($user['patronymic']); ?>">
                </div>
                <div class="block_7">
                    <p id="block_6_type">Роль:</p>
                    <input type="text" id="block_6_text" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                </div>
                <div class="block_7">
                    <p id="block_6_type">Username:</p>
                    <input type="text" id="block_6_text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
            </div>
            <?php if($user['role'] === 'manager'): ?>
            <div class="block_8">
                <label for="view_restrict" id="block_8_restrict">Сотруднику видны только те задачи, где он является исполнителем?</label>
                <select name="view_restrict" id="view_restrict" required>
                    <option value="no" <?php echo ($user['view_restrict'] == 'no') ? 'selected' : ''; ?>>Нет</option>
                    <option value="yes" <?php echo ($user['view_restrict'] == 'yes') ? 'selected' : ''; ?>>Да</option>
                </select><br>
            </div>
            <?php endif; ?>
            <button id="change_project" class="pattern_button_2">Сохранить</button>
        </div>
    </form>
    <div class="footer_block_new">
        <div class="footer-content">
          <meta content="2025">
          <meta content="YouProject">
          <p class="copyright">© 2025 YouProject. Все права защищены.</p>
          <div class="social-links">
            <a target="_blank" href="https://vk.com/temastraik" target="_blank"><img src="SVG/vk.svg" alt="Вконтакте"></a>
            <a target="_blank" href="https://t.me/temastraik" target="_blank"><img src="SVG/telegram.svg" alt="Telegram"></a>
            <a target="_blank" href="mailto:artm.korablv.07@gmail.ru" target="_blank"><img src="SVG/mail.svg" alt="Telegram"></a>
          </div>
        </div>
            <img src="SVG/up-arrow.svg" class="scroll-to-top">
    </div>
</body>
</html>