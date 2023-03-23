<?php

require_once __DIR__ . '/classes/Seminar.php';
require_once __DIR__ . '/inc/db.php';

session_start();
if (empty($_POST)) {
    $_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];
}

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$seminar = null;
$queryString = '';
$addHomeworkHtml = '';


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

    $addHomeworkHtml = '<div class="checkbox_box">
    <div class="field">
        <label for="homework">Add homework</label>
        <input type="checkbox" id="homework" class="homework clickableBox" >
        <div id="homeworkSubMenu" style="display: none">
            <form method="post">
                <div class="field">
                    <label for="Name">Name: </label>
                    <input type="text" name="Name" id="Name" placeholder="ex. Hello World!" pattern="^\S+(\s)?\S*$" required>
                </div>
                <div class="field">
                    <label for="Description" >Description:</label>
                    <textarea name="Description" id="Description" cols="40" rows="6" placeholder="ex. Print \'Hello world!\' on standard output." required></textarea>
                </div>
                <div class="field">
                    <label for="Marking">Marking:</label>
                    <textarea name="Marking" id="Marking" cols="40" rows="12" placeholder=\'ex. {
  "maximum": 1,
  "marking": [
      {"text": "Hello World!",
        "weight": "0.5"
      },
      {"text": "How are you?",
        "weight": "0.5"
      }
  ]
}\'  required></textarea>
                    <!--Marking regular expression: ^{\s*\"maximum\":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)+(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$-->
                </div>
                <div class="field">
                    <label for="Stdin">Stdin: </label>
                    <input type="file" name="Stdin" id="Stdin">
                </div>
                <div class="field">
                    <label for="Visible">Visibility: </label>
                    <input type="checkbox" name="Visible" id="Visible" value="true" >
                </div>
                <button type="submit" name="addHomework" value="true" >Add homework</button>
            </form>
        </div>
    </div>
</div>';
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

$courseData = $courseDataQuery->fetch(PDO::FETCH_ASSOC);

$homeworksDataQuery = $db->prepare('SELECT Homework.*, SeminarHomework.Visible FROM SeminarHomework INNER JOIN Homework ON
        SeminarHomework.HomeworkId=Homework.HomeworkId AND SeminarHomework.SeminarId=:SeminarId;');

$homeworksDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"]
]);

$homeworksData = $homeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);

if (!empty($courseData)) {
    $seminar = new classes\Seminar(array("SeminarId" => $_GET["SeminarId"], 'Day' => $courseData['Day'], 'TimeStart' => $courseData['TimeStart'], 'TimeEnd' => $courseData['TimeEnd'], "homeworks" => $homeworksData));
}

if (is_null($seminar)) {
    header('Location: /error/404');
    exit();
}

if (!empty($_POST)) {
    if (isset($_POST['addHomework']) && $_POST['addHomework'] = 'true') {
        if (empty(trim($_POST['Name']))) {
            $errors['Name'] = 'You have to set some name of homework.';
        }
        if (empty(trim($_POST['Description']))) {
            $errors['Description'] = 'You have to set some description of homework.';
        }
        if (empty(trim($_POST['Marking']))) {
            $errors['Marking'] = 'You have to set some marking for homework.';
        }
//        var_dump(array($_POST['Name'],$_POST['Description'], $_POST['Marking'], $_SESSION['UserId'], $_POST['InputFile']) );
        if (empty($errors)) {
            $visible = 0;
            $db->beginTransaction();
            $saveHomeworkQuery = $db->prepare('INSERT INTO Homework (Name, Description, Marking, AddedBy, InputFile, General)
                                VALUES (:Name, :Description, :Marking, :AddedBy, :InputFile, 0);');
            $saveHomeworkQuery->execute([
                ':Name' => $_POST['Name'],
                ':Description' => $_POST['Description'],
                ':Marking' => $_POST['Marking'],
                ':AddedBy' => $_SESSION['UserId'],
                ':InputFile' => $_POST['InputFile']
            ]);

            $homeworkId = $db->lastInsertId();

            if (isset($_POST['Visible']) && $_POST['Visible'] == 'true') {
                $visible = 1;
            }

            $saveSeminarHomeworkQuery = $db->prepare('INSERT INTO SeminarHomework (SeminarId, HomeworkId, Visible)
                                        VALUES (:SeminarId, :HomeworkId, :Visible);');
            $saveSeminarHomeworkQuery->execute([
                ':SeminarId' => $seminar->getSeminarId(),
                ':HomeworkId' => $homeworkId,
                ':Visible' =>  $visible
            ]);
            $db->commit();

            unset($_POST['addHomework']);
            header('Location: ' . $_SESSION['rdrurl']);
        }
    }
}

//$seminarBreadCrumb = 'Seminar of '  . $courseData[0]["Ident"] . ' (' . date_format(date_create($courseData[0]["TimeStart"]),"H:i") . '-' . date_format(date_create($courseData[0]["TimeEnd"]),"H:i") . ' on ' . date_format(date_create($courseData[0]["Day"]), "l") . ")";
//$_SESSION["seminarBreadCrumb"] = $seminarBreadCrumb;
//$seminarBreadCrumb
$seminarInfo = htmlspecialchars($courseData['Ident']). ' in ' . htmlspecialchars($courseData['Semester']) . ' in ' . htmlspecialchars($courseData['Year']) . ' at ' . date_format(date_create($courseData["TimeStart"]),"H:i") . '-' . date_format(date_create($courseData["TimeEnd"]),"H:i") . ' on ' . date_format(date_create($courseData["Day"]), "l");

echo '<div class="breadcrumb_div">
            <div class="breadcrumbPath">
                <a href="/">Home</a>
                <p class="arrow">→</p>
            </div>
            <div class="breadcrumbPath">
                <p> ' . 'Seminar (' . $seminarInfo . ')' . '</p>
            </div>
    </div>';

print($seminar);

echo $addHomeworkHtml;

include __DIR__ . '/inc/footer.php';