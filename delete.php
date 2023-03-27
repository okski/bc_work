<?php
require_once __DIR__ . '/inc/db.php';
session_start();


if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

if (empty($_POST)) {
    header('Location: /error/404');
    exit();
}

if (isset($_POST['HomeworkId'])) {
    $queryString = 'SELECT * FROM Homework WHERE Homework.HomeworkId=:HomeworkId AND Homework.AddedBy=:UserId LIMIT 1;';

    $homeworkQuery = $db->prepare($queryString);

    $homeworkQuery->execute([
        ':HomeworkId' => $_POST['HomeworkId'],
        ':UserId' => $_SESSION['UserId']
    ]);

    if ($homeworkQuery->rowCount()!=1) {
        header('Location: /error/404');
        exit();
    }

    $queryString = 'DELETE FROM Homework WHERE Homework.HomeworkId=:HomeworkId AND Homework.AddedBy=:UserId LIMIT 1;';

    $homeworkDeleteQuery = $db->prepare($queryString);

    $homeworkDeleteQuery->execute([
        ':HomeworkId' => $_POST['HomeworkId'],
        ':UserId' => $_SESSION['UserId']
    ]);

    header('Location: ' . substr($_SESSION['rdrurl'], 0, strpos($_SESSION['rdrurl'], '/homework')));
    exit();
}
