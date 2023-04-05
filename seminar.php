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


if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, Seminar.TimeStart, Seminar.TimeEnd, Seminar.Day, Seminar.TeacherId
from Seminar INNER JOIN TeachedCourse ON TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId AND 
Seminar.SeminarId=:SeminarId INNER JOIN Course ON Course.CourseId=TeachedCourse.CourseId INNER JOIN SeminarStudent
ON SeminarStudent.SeminarId=Seminar.SeminarId AND SeminarStudent.StudentId=:UserId LIMIT 1;';
} elseif(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, Seminar.TimeStart, Seminar.TimeEnd, Seminar.Day, Seminar.TeacherId
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

$courseData = $courseDataQuery->fetch(PDO::FETCH_ASSOC);

$homeworksDataQuery = $db->prepare('SELECT Homework.*, SeminarHomework.Visible FROM SeminarHomework INNER JOIN Homework ON
        SeminarHomework.HomeworkId=Homework.HomeworkId AND SeminarHomework.SeminarId=:SeminarId;');

$homeworksDataQuery->execute([
    ':SeminarId' => $_GET["SeminarId"]
]);

$homeworksData = $homeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);

if (!empty($courseData)) {
    $seminar = new classes\Seminar(array("SeminarId" => $_GET["SeminarId"], 'TeacherId' => $courseData['TeacherId'], 'Day' => $courseData['Day'], 'TimeStart' => $courseData['TimeStart'], 'TimeEnd' => $courseData['TimeEnd'], "homeworks" => $homeworksData));
}

if (is_null($seminar)) {
    header('Location: /error/404');
    exit();
}

if (!empty($_POST)) {
    if ($seminar->getTeacherId() != $_SESSION['UserId']) {
        header('Location: /error/404');
        exit();
    }
    json_decode($_POST['Marking']);
    if (isset($_POST['addHomework']) && $_POST['addHomework'] = 'true') {
        if (empty(trim($_POST['Name']))) {
            $errors['Name'] = 'You have to set some name of homework.';
        }
        if (empty(trim($_POST['Description']))) {
            $errors['Description'] = 'You have to set some description of homework.';
        }
        if (empty(trim($_POST['Marking']))) {
            $errors['Marking'] = 'You have to set some marking for homework.';
        } elseif (json_last_error() !== JSON_ERROR_NONE) {
            $errors['Marking'] = 'Invalid JSON format.';
        } elseif (!preg_match('/^{\s*"maximum":\s*[1-9]+,\s*"marking":\s*\[\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*},\s*)*(?!,)\s*({"text":\s*".*",\s*"weight":\s*"\d(.\d+)?"\s*}\s*)\s*]\s*}$/' , $_POST['Marking'])) {
            $errors['Marking'] = 'Does not match wanted JSON structure.';
        }
//        var_dump(array($_POST['Name'],$_POST['Description'], $_POST['Marking'], $_SESSION['UserId'], $_POST['InputFile']) );
        if (empty($errors)) {
            $inputFile = null;
            if (isset($_FILES['InputFile']) && !empty($_FILES['InputFile']['name'])) {
                $inputFile = substr(file_get_contents($_FILES['InputFile']['tmp_name']), 0, $_FILES['InputFile']['size']);
            }

            $visible = 0;
            $db->beginTransaction();
            $saveHomeworkQuery = $db->prepare('INSERT INTO Homework (Name, Description, Marking, AddedBy, InputFile, General)
                                VALUES (:Name, :Description, :Marking, :AddedBy, :InputFile, 0);');
            $saveHomeworkQuery->execute([
                ':Name' => $_POST['Name'],
                ':Description' => $_POST['Description'],
                ':Marking' => $_POST['Marking'],
                ':AddedBy' => $_SESSION['UserId'],
                ':InputFile' => $inputFile
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
if ($_SESSION["Teacher"] == 1) {
    echo '<h1>Seminar</h1><div class="content">';
} elseif ($_SESSION["Student"] == 1) {
    echo '<h1>Seminar</h1><div class="content"><div class="homeworksHeader">List of homeworks</div>';
}

//    echo '<div class="content">';

if ($_SESSION["Teacher"] == 1 && $seminar->getTeacherId() == $_SESSION['UserId']) {
    echo $addHomeworkHtml = '<div class="checkbox_box">
        <label for="homework">Add homework</label>
        <input type="checkbox" id="homework" class="clickableBox" ';
    if (!empty($errors)) echo 'checked';
    echo '>
        <div id="homeworkSubMenu" ';
    if (!empty($errors)) echo 'style="display: block"'; else echo 'style="display: none"';
    echo '>
            <form method="post" enctype="multipart/form-data" name="homeworkForm">
                <div class="field">
                    <label for="Name">Name: </label>
                    <input type="text" name="Name" id="Name" placeholder="ex. Hello World!" pattern="^\S+(\s)?\S*$" required ';
    if (!empty($errors)) echo 'value="'.htmlspecialchars($_POST['Name']).'"';
    echo '>';
    if (!empty($errors['Name'])) echo '<div class="text-danger">' . $errors['Name'] . '</div>';
    echo '      </div>
                <div class="field">
                    <label for="Description" >Description:</label>
                    <textarea name="Description" id="Description" cols="40" rows="6" placeholder="ex. Print \'Hello world!\' on standard output." required>';
    if (!empty($errors)) echo htmlspecialchars($_POST['Description']);
    echo '</textarea>';
    if (!empty($errors['Description'])) echo '<div class="text-danger">' . $errors['Description'] . '</div>';
    echo '      </div>
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
}\'  required>';
    if (!empty($errors)) echo htmlspecialchars($_POST['Marking']);
    echo '</textarea>';

    echo '<div class="text-danger" ';
    if (!empty($errors['Marking'])) echo '>' . $errors['Marking']; else echo 'style="display: none">';
    echo '</div>
                </div>
                <div class="field">
                    <label for="InputFile">Input: </label>
                    <input type="file" name="InputFile" id="InputFile">
                </div>
                <div class="field">
                    <label for="Visible">Visibility: </label>
                    <input type="checkbox" name="Visible" id="Visible" value="true" ';
    if (!empty($errors) && $_POST['Visible'] == 'true') echo 'checked';
    echo '>
                </div>
                <button type="submit" name="addHomework" value="true" >Add homework</button>
            </form>
        </div>
    </div>

<div class="homeworksHeader">List of homeworks</div>';
}

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    echo $seminar->toString('Student');
}elseif (isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    echo $seminar->toString('Teacher');
}

echo '</div>';

include __DIR__ . '/inc/footer.php';