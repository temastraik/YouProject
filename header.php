<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="SVG/logo.svg">
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
</head>
<body>
    <?php
    session_start();
    $db = new SQLite3('database.db');
    
    // Проверяем, авторизован ли пользователь
    $isLoggedIn = isset($_SESSION['user_id']);
    
    // Обработка выхода из системы
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: project.php');
        exit();
    }
    ?>
    
    <header>
        <div class="block_header">
            <img src="SVG/logo.svg" id='logo' alt='Логотип'>
            <a href="index.php" id="header_href_index">YouProject</a>
            <nav class="header-nav">
                <ul>
                    <li>
                        <a href="project.php" class="<?php echo ($current_page == 'project.php' || $current_page == 'task_view.php' || $current_page == 'task_edit.php' || $current_page == 'task_create.php') ? 'active' : ''; ?>">Проекты</a>
                    </li>
                    <li>
                        <a href="plan.php" class="<?php echo ($current_page == 'plan.php') ? 'active' : ''; ?>">Планировщик</a>
                    </li>
                    <li>
                        <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">Личный кабинет</a>
                    </li>
                </ul>
            </nav>
            <?php if ($isLoggedIn): ?>
                <button onclick="window.location.href='?logout=1'" class="pattern_button_1" id="header_log">Выйти</button>
            <?php else: ?>
                <button onclick="window.location.href='#login'" class="pattern_button_1" id="header_log">Войти</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Авторизация пользователя -->
    <?php if (!$isLoggedIn): ?>
    <div id="login">
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $db->prepare('SELECT id, password FROM Users WHERE username = :username');
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $result = $stmt->execute();
            $user = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: project.php');
                exit();
            } else {
                $error = "Неверное имя пользователя или пароль.";
            }
        }
        ?>
        <div id="window_login" class="pattern_modal">
            <a href="#"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
            <p id='name_autorization' class="pattern_heading">Вход в систему</p>
            <form method="POST">
                <label>username:</label>
                <input type="text" name="username" id="login_username" class="pattern_input" required maxlength="10"><br>
                <label>пароль:</label>
                <input type="password" name="password" id="login_password" class="pattern_input" required><br>
                <input type="submit" value="Войти" id="login_submit" class="pattern_button_2">
            </form>
            <p id='choice_window'>или</p>
            <button onclick="window.location.href='#register'" id='login_register' class="pattern_button_1">Зарегистрироваться</button>
            <?php if (isset($error)) echo "<p style='color:red; text-align:center; margin-top:-217px;'>$error</p>"; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Регистрация пользователя -->
    <?php if (!$isLoggedIn): ?>
    <div id="register">
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $company = $_POST['company'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $company)) {
                $error = "Название компании может содержать только буквы";
            } elseif ($password !== $confirm_password) {
                $error = "Пароли не совпадают";
            } elseif (strlen($username) > 10) {
                $error = "Username должен быть не больше 10 символов";
            } elseif (strlen($company) > 10) {
                $error = "Название компании должно быть не больше 10 символов";
            } else {
                $stmt = $db->prepare('SELECT * FROM Users WHERE username = :username');
                $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                $result = $stmt->execute();
                
                if ($result->fetchArray(SQLITE3_ASSOC)) {
                    $error = "Имя пользователя занято";
                } else {
                    $stmt = $db->prepare('SELECT id FROM Company WHERE name = :name');
                    $stmt->bindValue(':name', $company, SQLITE3_TEXT);
                    $result = $stmt->execute();
                    $company_data = $result->fetchArray(SQLITE3_ASSOC);
                    
                    if ($company_data) {
                        $error = "Компания с таким названием уже существует. Выберите другое название.";
                    } else {
                        $stmt = $db->prepare('INSERT INTO Company (name) VALUES (:name)');
                        $stmt->bindValue(':name', $company, SQLITE3_TEXT);
                        $stmt->execute();
                        $company_id = $db->lastInsertRowID();
                        
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare('INSERT INTO Users (username, role, password, company_id) VALUES (:username, :role, :password, :company_id)');
                        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
                        $stmt->bindValue(':role', 'manager', SQLITE3_TEXT); 
                        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
                        $stmt->bindValue(':company_id', $company_id, SQLITE3_INTEGER);
                        $stmt->execute();
                        
                        header('Location: #login');
                        exit();
                    }
                }
            }
        }
        ?>
        <div id="window_register" class="pattern_modal">
            <a href="#"><img src="SVG/cross.svg" alt="Закрыть окно" class="close"></a>
            <p id="name_registration" class="pattern_heading">Регистрация менеджера</p>
            <form method="POST">
                <label>username:</label>
                <input type="text" name="username" id="register_username" class="pattern_input" placeholder="до 10 символов" required maxlength="10"><br>
                <label>компания:</label>
                <input type="text" name="company" id="register_company" class="pattern_input"placeholder="до 10 символов" required maxlength="10"><br>
                <label>пароль:</label>
                <input type="password" name="password" id="register_password" class="pattern_input" required maxlength="40"><br>
                <label>повторите пароль:</label>
                <input type="password" name="confirm_password" id="register_confirm_password" class="pattern_input" required maxlength="40"><br>
                <input type="submit" value="Зарегистрироваться" id="register_submit" class="pattern_button_2">
            </form>
            <p id="choice_window">или</p>
            <button onclick="window.location.href='#login'" id="register_login" class="pattern_button_1">Войти</button>
            <?php if (isset($error)) echo "<p style='color:red; text-align:center; margin-top:-217px;'>$error</p>"; ?>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>