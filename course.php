<?php
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$errors = array();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$querySting = "";

if(!isset($_SESSION["Teacher"]) || $_SESSION["Teacher"] != 1) {
    header('Location: /error/404');
    exit();
}
$querySting = 'SELECT Course.Ident, TeachedCourse.TeachedCourseId, TeachedCourse.Year, TeachedCourse.Semester, TeachedCourse.Guarantor FROM TeachedCourse INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId WHERE TeachedCourse.Year=:Year AND TeachedCourse.Semester=:Semester AND Course.Ident=:Ident AND TeachedCourse.Guarantor=:UserId LIMIT 1;';

$courseDataQuery = $db->prepare($querySting);

$courseDataQuery->execute([
    ':Year' => $_GET['Year'].'/'.(substr($_GET['Year'], 2, 2) +1),
    ':Semester' => $_GET['Semester'],
    ':Ident' => $_GET['Ident'],
    ':UserId' => $_SESSION['UserId']
]);

if ($courseDataQuery->rowCount()!=1) {
    header('Location: /error/404');
    exit();
}

$courseData = $courseDataQuery->fetch(PDO::FETCH_ASSOC);

$seminarsQuery = $db->prepare('SELECT SeminarId FROM Seminar WHERE TeachedCourseId=:TeachedCourseId;');

$seminarsQuery->execute([
    ':TeachedCourseId' => $courseData['TeachedCourseId']
]);

$seminars = $seminarsQuery->fetchAll(PDO::FETCH_ASSOC);

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
                                VALUES (:Name, :Description, :Marking, :AddedBy, :InputFile, 1);');
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

            foreach ($seminars as $seminar) {
                $saveSeminarHomeworkQuery = $db->prepare('INSERT INTO SeminarHomework (SeminarId, HomeworkId, Visible)
                                            VALUES (:SeminarId, :HomeworkId, :Visible);');
                $saveSeminarHomeworkQuery->execute([
                    ':SeminarId' => $seminar['SeminarId'],
                    ':HomeworkId' => $homeworkId,
                    ':Visible' =>  $visible
                ]);
            }

            $db->commit();

            unset($_POST['addHomework']);
            header('Location: ' . $_SESSION['rdrurl']);
        }
    }
}

?>
<div class="breadcrumb_div">
    <div class="breadcrumbPath">
        <a href="/">Home</a>
        <p class="arrow">→</p>
    </div>
    <div class="breadcrumbPath">
        <p>Course (<?php echo htmlspecialchars($courseData['Ident']). ' in ' . htmlspecialchars($courseData['Semester']) . ' in ' . htmlspecialchars($courseData['Year']) ?>)</p>
    </div>
</div>


<div class="checkbox_box">
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
                    <textarea name="Description" id="Description" cols="40" rows="6" placeholder="ex. Print 'Hello world!' on standard output." required></textarea>
                </div>
                <div class="field">
                    <label for="Marking">Marking:</label>
                    <textarea name="Marking" id="Marking" cols="40" rows="12" placeholder='ex. {
  "maximum": 1,
  "marking": [
      {"text": "Hello World!",
        "weight": "0.5"
      },
      {"text": "How are you?",
        "weight": "0.5"
      }
  ]
}'  required></textarea>
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
</div>
<br>
<div>list of homeworks</div>

<?php
$values = array_map('array_pop', $seminars);
$imploded = implode(',', $values);

$homeworksQuery = $db->prepare('SELECT Homework.* FROM SeminarHomework JOIN Seminar ON
    Seminar.SeminarId=SeminarHomework.SeminarId JOIN Homework ON
    Homework.HomeworkId=SeminarHomework.HomeworkId WHERE Seminar.SeminarId IN (\'' . $imploded . '\') AND Homework.AddedBy=:UserId AND Homework.General=1;');

$homeworksQuery->execute([
    ':UserId' => $_SESSION['UserId']
]);

$homeworksData = $homeworksQuery->fetchAll(PDO::FETCH_ASSOC);

if (empty($homeworksData)) {
    echo 'There are no homeworks.';
}

echo '<div class="homeworks">';
foreach ($homeworksData as $homeworkData) {
    echo '<div class="homework">
<div class="name">' . htmlspecialchars($homeworkData['Name']) . '</div>
<div class="shortDescription">' . htmlspecialchars(substr($homeworkData['Description'], 0, 25)) . '</div>
<form action="'.$_SESSION['rdrurl'].'/edit" method="post">
<input type="hidden" name="HomeworkId" id="HomeworkId" value="' . $homeworkData['HomeworkId'] . '">
<button type="submit">Edit</button>
</form>
</div>';
}

echo '</div>';
include __DIR__ . '/inc/footer.php';