<?php
require_once 'functions.php';

$user_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Обработка выбора месяца/года
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_month']) && isset($_POST['selected_year'])) {
    $month = (int)$_POST['selected_month'];
    $year = (int)$_POST['selected_year'];
    header("Location: ?month=$month&year=$year");
    exit();
}

// Обработка добавления события
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $recurrence = $_POST['recurrence'] ?? 'none';
    $recurrence_end_date = !empty($_POST['recurrence_end_date']) ? $_POST['recurrence_end_date'] : null;
    
    if (strlen($event_date) == 10) {
        $event_date .= ' 00:00';
    }
    
    $stmt = $db->prepare("INSERT INTO Events (event_name, event_date, user_id, recurrence_pattern, recurrence_end_date) VALUES (:name, :date, :user_id, :recurrence, :end_date)");
    $stmt->bindValue(':name', $event_name, SQLITE3_TEXT);
    $stmt->bindValue(':date', $event_date, SQLITE3_TEXT);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':recurrence', $recurrence, SQLITE3_TEXT);
    $stmt->bindValue(':end_date', $recurrence_end_date, SQLITE3_TEXT);
    $stmt->execute();
    
    header("Location: ?month=$month&year=$year");
    exit();
}

// Получение данных для календаря
$month_formatted = $month < 10 ? '0' . $month : $month;
$start_date = "$year-$month_formatted-01";
$end_date = "$year-$month_formatted-" . days_in_month($month, $year);

$tasks = getTasksForMonth($db, $user_id, $month, $year);
$all_events = getEventsForMonth($db, $user_id, $start_date, $end_date);

// Разделяем обычные и повторяющиеся события
$events = [];
$recurring_events_data = [];
foreach ($all_events as $day => $day_events) {
    foreach ($day_events as $event) {
        if ($event['recurrence_pattern'] === 'none') {
            $events[$day][] = $event;
        } else {
            $recurring_events_data[] = $event;
        }
    }
}

$recurring_events = processRecurringEvents($recurring_events_data, $start_date, $end_date);

// Навигация по месяцам
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}
?>