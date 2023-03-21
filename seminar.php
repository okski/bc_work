<?php
require_once __DIR__ . '/classes/Seminar.php';
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
$queryString = '';

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    if (isset($_GET["GuarantorId"]) && !empty($_GET["GuarantorId"])) {
        header('Location: /error/404');
        exit();
    }
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, Seminar.TimeStart, Seminar.TimeEnd, Seminar.Day
from Seminar INNER JOIN TeachedCourse ON TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId AND 
Seminar.SeminarId=:SeminarId INNER JOIN Course ON Course.CourseId=TeachedCourse.CourseId INNER JOIN SeminarStudent
ON SeminarStudent.SeminarId=Seminar.SeminarId AND SeminarStudent.StudentId=:UserId LIMIT 1;';
} elseif(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, Seminar.TimeStart, Seminar.TimeEnd, Seminar.Day
from Seminar INNER JOIN TeachedCourse ON TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId AND 
Seminar.SeminarId=:SeminarId AND Seminar.TeacherId=:UserId INNER JOIN Course ON Course.CourseId=TeachedCourse.CourseId LIMIT 1;';
} else {
    header('Location: /error/404');
    exit();
}

$courseDataQuery = $db->prepare($queryString);

$courseDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"],
    ':UserId' => $_SESSION["UserId"]
]);

if ($courseDataQuery->rowCount()!=1) {
    header('Location: /error/404');
    exit();
}

$courseData = $courseDataQuery->fetchAll(PDO::FETCH_ASSOC);


$homeworksDataQuery = $db->prepare('SELECT Homework.*, SeminarHomework.Visible FROM SeminarHomework INNER JOIN Homework ON
        SeminarHomework.HomeworkId=Homework.HomeworkId AND SeminarHomework.SeminarId=:SeminarId;');

$homeworksDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"]
]);

$homeworksData = $homeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);

if (!empty($courseData)) {
    $seminar = new classes\Seminar(array("SeminarId" => $_GET["SeminarId"], "homeworks" => $homeworksData));
}

if (is_null($seminar)) {
    header('Location: /error/404');
    exit();
}

//$seminarBreadCrumb = 'Seminar of '  . $courseData[0]["Ident"] . ' (' . date_format(date_create($courseData[0]["TimeStart"]),"H:i") . '-' . date_format(date_create($courseData[0]["TimeEnd"]),"H:i") . ' on ' . date_format(date_create($courseData[0]["Day"]), "l") . ")";
//$_SESSION["seminarBreadCrumb"] = $seminarBreadCrumb;
//$seminarBreadCrumb

echo '<div class="breadcrumb_div">
            <div class="breadcrumbPath">
                <a href="/">Home</a>
                <p class="arrow">→</p>
            </div>
            <div class="breadcrumbPath">
                <p> ' . 'Seminar (' . htmlspecialchars($_GET["SeminarId"]) . ')' . '</p>
            </div>
    </div>';

echo "<div>" . htmlspecialchars($courseData["ident"]) . "</div>";
print($seminar);

include __DIR__ . '/inc/footer.php';