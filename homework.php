<?php
require_once __DIR__.'/classes/Homework.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: login.php');
    exit();
}

include __DIR__ . '/inc/header.php';

$homework = null;


$homeworkDataQuery = $db->prepare('SELECT Homework.* FROM Homework WHERE HomeworkId=:HomeworkId LIMIT 1;');


$homeworkDataQuery->execute([
    ':HomeworkId' => $_GET["HomeworkId"]
]);
$homeworkData = $homeworkDataQuery->fetch(PDO::FETCH_ASSOC);

if (!empty($homeworkData)) {
    $homework = new \classes\Homework($homeworkData);
}


if (is_null($homework)) {
    header('Location: /error/404.html');
}


echo '<div class="breadcrumb_div">
        <div class="breadcrumbPath">
            <a href="/">Home</a>
            <p class="arrow">→</p>
        </div>
        <div class="breadcrumbPath">
            <a href="/seminar/' . htmlspecialchars($_GET["SeminarId"]) . '">Seminar (' . htmlspecialchars($_GET["SeminarId"]) . ')</a>
            <p class="arrow">→</p>
        </div>
        <div class="breadcrumbPath">
            <p>' . $homework->getName() . '</p>
        </div>
    </div>';

$homework->printHomework();

include __DIR__ . '/inc/footer.php';