<?php
function fetchUserData($db, $user_id) {
    $stmt = $db->prepare("SELECT username, first_name, last_name, patronymic, email, company_id, id, role, view_restrict FROM Users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
    $user_info = $stmt->execute();
    $user = $user_info->fetchArray(SQLITE3_ASSOC);
    
    if (!$user) {
        die("Ошибка: пользователь не найден");
    }
    
    return $user;
}

function fetchProjects($db, $user_id) {
    $stmt = $db->prepare("SELECT * FROM Projects WHERE company_id = (SELECT company_id FROM Users WHERE id = :user_id)");
    $stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function fetchTasks($db) {
    $tasks = [];
    $stmt = $db->prepare("SELECT * FROM Tasks");
    $result = $stmt->execute();
    while ($task = $result->fetchArray(SQLITE3_ASSOC)) {
        $tasks[] = $task;
    }
    return $tasks;
}

function fetchUsers($db, $user_id) {
    $stmt = $db->prepare("SELECT id, username FROM Users WHERE company_id = (SELECT company_id FROM Users WHERE id = :user_id)");
    $stmt->bindParam(':user_id', $user_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function fetchCompanyMembers($db, $company_id) {
    $stmt = $db->prepare("SELECT id, username, role FROM Users WHERE company_id = :company_id");
    $stmt->bindParam(':company_id', $company_id, SQLITE3_INTEGER);
    return $stmt->execute();
}

function fetchAllUsers($db) {
    return $db->query("SELECT id, username, view_restrict, role FROM Users");
}

function fetchManagerData($db, $company_id) {
    $stmt = $db->prepare("SELECT view_restrict FROM Users WHERE role = 'manager' AND company_id = :company_id");
    $stmt->bindParam(':company_id', $company_id, SQLITE3_INTEGER);
    $users_management = $stmt->execute();
    return $users_management->fetchArray(SQLITE3_ASSOC);
}
?>