<?php
require_once 'notes_handlers.php';
require_once 'calendar_handlers.php';

$russian_months = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Календарь задач и событий</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/tooltips.js" defer></script>
    <script src="js/recuerrence.js" defer></script>
</head>
<body>
    <div class="block_plan_calendar">
        <!-- Форма заметок -->
        <div class="form-container">
            <form method="POST">
                <p id="notes_block_header" class="pattern_heading"><?= $edit_note ? 'Редактировать заметку' : 'Добавить заметку' ?></p>
                <?php if ($edit_note): ?>
                    <input type="hidden" name="id" value="<?= $edit_note['id'] ?>">
                <?php endif; ?>
                <div id="form_container_label_1">
                    <input type="text" id="note_name" name="note_name" placeholder="Заголовок (до 25 символов)"
                        value="<?= $edit_note ? htmlspecialchars($edit_note['note_name']) : '' ?>" 
                        maxlength="25" required>
                </div>
                <div id="form_container_label_2">
                    <textarea id="description" name="description" class="description-input" placeholder="Текст (до 500 символов)"
                       maxlength="500"><?= $edit_note ? htmlspecialchars($edit_note['description']) : '' ?></textarea>
                </div>
                <button id="add_note" type="submit" name="<?= $edit_note ? 'update_note' : 'add_note' ?>" class="pattern_button_2">
                    <?= $edit_note ? 'Обновить' : 'Добавить' ?>
                </button>
                <?php if ($edit_note): ?>
                    <div class="add_note_a_button">
                        <a href="?" id="add_note_a">Отмена</a>
                    </div>
                <?php endif; ?>
            </form>
            <?php if (isset($error_add_note)): ?>
                <p style='color:red; text-align:center; margin-top:-88px;'><?= $error_add_note ?></p>
            <?php endif; ?>
        </div>

        <div class="calendar-wrapper">
            <!-- Форма добавления события -->
            <div id="add_event_calendar">
                <p id="calendar-title" class="pattern_heading">Добавить событие</p>
                <form method="post">
                    <input type="text" name="event_name" placeholder="Название (до 25 символов)" maxlength="25" required id="add-event-form-input">
                    <input type="datetime-local" name="event_date" id="add-event-form-input"
                           value="<?= date('Y-m-d\TH:i', strtotime('today')) ?>" 
                           min="<?= date('Y-m-d\TH:i', strtotime('today')) ?>" required>
                    
<select name="recurrence" id="add-event-form-select">
    <option value="none">Без повторений</option>
    <option value="daily:1">Ежедневно</option>
    <option value="weekly:1">Еженедельно</option>
    <option value="monthly:1">Ежемесячно</option>
</select>

<div id="recurrence-fields" style="display: none;">
    <label>Завершить повторение:</label>
    <input type="date" name="recurrence_end_date" id="add-event-form-input" min="<?= date('Y-m-d') ?>">
</div>
                    
                    <button type="submit" name="add_event" id="add_event" class="pattern_button_2">Добавить</button>
                </form>
            </div>

            <div class="hr_vertical"></div>

            <!-- Календарь -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>">
                        <button id="calendar-header-button">&lt;</button>
                    </a>
                    <div id="calendar-title" class="pattern_heading">
                        <?= $russian_months[$month] . ' ' . $year ?>
                    </div>
                    <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>">
                        <button id="calendar-header-button">&gt;</button>
                    </a>
                </div>
                
                <div class="calendar-grid">
                    <div class="calendar-day-header">Пн</div>
                    <div class="calendar-day-header">Вт</div>
                    <div class="calendar-day-header">Ср</div>
                    <div class="calendar-day-header">Чт</div>
                    <div class="calendar-day-header">Пт</div>
                    <div class="calendar-day-header">Сб</div>
                    <div class="calendar-day-header">Вс</div>
                    
                    <?php
                    $days_in_month = days_in_month($month, $year);
                    $first_day = first_day_of_month($month, $year);
                    $first_day = $first_day == 0 ? 6 : $first_day - 1;
                    
                    for ($i = 0; $i < $first_day; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }
                    
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $is_today = ($day == date('j') && $month == date('n') && $year == date('Y')) ? 'today' : '';
                        $day_of_week = date('w', mktime(0, 0, 0, $month, $day, $year));
                        $is_weekend = ($day_of_week == 0 || $day_of_week == 6) ? 'weekend' : '';
                        $is_holiday = is_russian_holiday($day, $month, $year) ? 'holiday' : '';
                        
                        echo '<div class="calendar-day ' . $is_today . ' ' . $is_weekend . ' ' . $is_holiday . '">';
                        echo '<div class="day-number">' . $day;
                        
                        echo '<div class="day-number-markers">';
                        
// Отметки для обычных событий
if (isset($events[$day])) {
    $event_tooltip_content = '<strong>События:</strong><br>';
    foreach ($events[$day] as $event) {
        $event_time = date('H:i', strtotime($event['event_date']));
        $event_tooltip_content .= '- ' . htmlspecialchars($event['event_name']) . ' в ' . $event_time . '<br>';
    }
    echo '<div class="event-marker" data-tooltip-content="' . htmlspecialchars($event_tooltip_content) . '"></div>';
}

// Отметки для повторяющихся событий
if (isset($recurring_events[$day])) {
    $recurring_tooltip_content = '<strong>Повторяющиеся события:</strong><br>';
    foreach ($recurring_events[$day] as $event) {
        $event_time = date('H:i', strtotime($event['event_date']));
        $recurring_tooltip_content .= '- ' . htmlspecialchars($event['event_name']) . ' в ' . $event_time . '<br>';
    }
    echo '<div class="recurring-marker" data-tooltip-content="' . htmlspecialchars($recurring_tooltip_content) . '"></div>';
}

// Отметки для задач
if (isset($tasks[$day])) {
    $tooltip_content = '<strong>Задачи:</strong><br>';
    foreach ($tasks[$day] as $task) {
        $deadline_time = date('H:i', strtotime($task['deadline']));
        $tooltip_content .= '- ' . htmlspecialchars($task['name']) . ' к ' . $deadline_time . '<br>';
    }
    echo '<div class="deadline-marker" data-tooltip-content="' . htmlspecialchars($tooltip_content) . '"></div>';
}
                        
                        echo '</div></div></div>';
                    }
                    ?>
                </div>
                
                <form method="post" class="month-selector">
                    <select name="selected_month" id="selected_month">
                        <?php foreach ($russian_months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $num == $month ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="selected_year" id="selected_year">
                        <?php for ($y = date('Y') -1; $y <= date('Y') + 5; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" id="go_month_year" class="pattern_button_2">Перейти</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Список заметок -->
    <div class="notes_block">
        <p id="notes_list_block_header" class="pattern_heading">Ваши заметки</p>

        <div class="filters_notes">
            <a href="?filter=newest">Новые</a> |
            <a href="?filter=oldest">Старые</a>
        </div>

        <?php if (empty($notes)): ?>
            <p style="text-align: center; font-size: 24px; padding-bottom: 20px;">Нет заметок</p>
        <?php else: ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($notes as $note): ?>
                    <li class="note">
                        <p id="header_title_note"><?= htmlspecialchars($note['note_name']) ?></p>
                        <p id="description_note"><?= nl2br(htmlspecialchars($note['description'])) ?></p>
                        <div class="actions">
                            <form method="GET" action="">
                                <input type="hidden" name="edit" value="<?= $note['id'] ?>">
                                <button type="submit" id="edit-btn" class="pattern_button_1">Редактировать</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить заметку \'<?= addslashes($note['note_name']) ?>\'?')">
                                <input type="hidden" name="id" value="<?= $note['id'] ?>">
                                <button type="submit" name="delete_note" id="delete_note" class="pattern_button_3">Удалить</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <!-- Элементы для подсказок -->
<div id="task-tooltip" class="task-tooltip"></div>
<div id="event-tooltip" class="task-tooltip"></div>
<div id="recurring-tooltip" class="recurring-tooltip"></div>
</body>
</html>