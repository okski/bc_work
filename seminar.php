<?php
require_once __DIR__.'/classes/Seminar.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$seminar = null;

$courseDataQuery = $db->prepare('SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId
from Seminar INNER JOIN TeachedCourse ON TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId AND 
Seminar.SeminarId=:SeminarId INNER JOIN Course ON Course.CourseId=TeachedCourse.CourseId LIMIT 1;');

$courseDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"]
]);

$courseData = $courseDataQuery->fetchAll(PDO::FETCH_ASSOC);


$homeworksDataQuery = $db->prepare('SELECT Homework.* FROM SeminarHomework INNER JOIN Homework ON
        SeminarHomework.HomeworkId=Homework.HomeworkId AND SeminarHomework.SeminarId=:SeminarId;');

$homeworksDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"]
]);

$homeworksData = $homeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);


if (!empty($courseData)) {
    $seminar = new classes\Seminar(array("SeminarId" => $_GET["SeminarId"], "homeworks" => $homeworksData));
}

if (is_null($seminar)) {
    header('Location: /error/404.html');
    exit();
}

echo '<div class="breadcrumb_div">
            <div class="breadcrumbPath">
                <a href="/">Home</a>
                <p class="arrow">→</p>
            </div>
            <div class="breadcrumbPath">
                <p>Seminar (' . htmlspecialchars($_GET["SeminarId"]) . ')</p>
            </div>
    </div>';

echo "<div>" . htmlspecialchars($courseData["ident"]) . "</div>";
print($seminar);

include __DIR__ . '/inc/footer.php';