<?php

function days_in_month($month, $year) {
    $month_formatted = $month < 10 ? '0' . $month : $month;
    return date('t', strtotime("$year-$month_formatted-01"));
}

function first_day_of_month($month, $year) {
    return date('w', mktime(0, 0, 0, $month, 1, $year));
}

function is_russian_holiday($day, $month, $year) {
    $fixed_holidays = [
        '01-01', '01-02', '01-03', '01-04', '01-05', '01-06', '01-07', '01-08',
        '02-23', '03-08', '05-01', '05-09', '06-12', '11-04'
    ];
    
    $date = sprintf('%02d-%02d', $month, $day);
    
    if (in_array($date, $fixed_holidays)) {
        return true;
    }
    
    $easter = date('m-d', easter_date($year));
    $easter_day = date('d', easter_date($year));
    $easter_month = date('m', easter_date($year));
    
    return ($month == $easter_month && $day == $easter_day);
}

function processRecurringEvents($events, $start_date, $end_date) {
    $recurring_events = [];
    
    foreach ($events as $event) {
        if ($event['recurrence_pattern'] === 'none') {
            continue;
        }

        try {
            $start = new DateTime($event['event_date']);
            $end = $event['recurrence_end_date'] ? new DateTime($event['recurrence_end_date']) : new DateTime('+1 year');
            $current_month_start = new DateTime($start_date);
            $current_month_end = new DateTime($end_date);
            
            $pattern = explode(':', $event['recurrence_pattern']);
            $type = $pattern[0];
            $value = isset($pattern[1]) ? (int)$pattern[1] : 1;
            
            $interval = null;
            $byday = null;
            
            switch ($type) {
                case 'daily':
                    $interval = new DateInterval("P{$value}D");
                    break;
                case 'weekly':
                    $interval = new DateInterval("P{$value}W");
                    $byday = $start->format('N'); // день недели (1-7)
                    break;
                case 'monthly':
                    $interval = new DateInterval("P{$value}M");
                    break;
                case 'yearly':
                    $interval = new DateInterval("P{$value}Y");
                    break;
                default:
                    continue 2; // пропускаем неизвестный тип
            }
            
            $period = new DatePeriod($start, $interval, $end);
            
            foreach ($period as $date) {
                // Для недельных событий проверяем, что день недели совпадает
                if ($type === 'weekly' && $date->format('N') != $byday) {
                    continue;
                }
                
                // Проверяем, что дата попадает в текущий месяц
                if ($date >= $current_month_start && $date <= $current_month_end) {
                    $event_day = (int)$date->format('j');
                    $event_copy = $event;
                    $event_copy['event_date'] = $date->format('Y-m-d H:i:s');
                    
                    if (!isset($recurring_events[$event_day])) {
                        $recurring_events[$event_day] = [];
                    }
                    $recurring_events[$event_day][] = $event_copy;
                }
            }
        } catch (Exception $e) {
            error_log("Error processing recurring event: " . $e->getMessage());
            continue;
        }
    }
    
    return $recurring_events;
}

function getTasksForMonth($db, $user_id, $month, $year) {
    $month_formatted = $month < 10 ? '0' . $month : $month;
    $tasks_query = "SELECT id, name, deadline FROM Tasks WHERE user_id = :user_id AND strftime('%Y-%m', deadline) = :month_year ORDER BY deadline";
    
    $stmt = $db->prepare($tasks_query);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':month_year', "$year-$month_formatted", SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $tasks = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $deadline_day = date('j', strtotime($row['deadline']));
        if (!isset($tasks[$deadline_day])) {
            $tasks[$deadline_day] = [];
        }
        $tasks[$deadline_day][] = $row;
    }
    
    return $tasks;
}

function getEventsForMonth($db, $user_id, $start_date, $end_date) {
    $events_query = "SELECT id, event_name, event_date, recurrence_pattern, recurrence_end_date FROM Events WHERE user_id = :user_id AND (
        (date(event_date) BETWEEN date(:start_date) AND date(:end_date)) OR
        (recurrence_pattern != 'none' AND date(event_date) <= date(:end_date) AND (recurrence_end_date IS NULL OR date(recurrence_end_date) >= date(:start_date)))
    ) ORDER BY event_date";
    
    $stmt = $db->prepare($events_query);
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':start_date', $start_date, SQLITE3_TEXT);
    $stmt->bindValue(':end_date', $end_date, SQLITE3_TEXT);
    $result = $stmt->execute();
    
    $events = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $event_day = date('j', strtotime($row['event_date']));
        $events[$event_day][] = $row;
    }
    
    return $events;
}
?>