<?php
require_once __DIR__ . '/classes/Course.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$error = array();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$querySting = "";
//
if(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $querySting = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, TeachedCourse.Guarantor FROM TeachedCourse INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId WHERE TeachedCourse.Year=:Year AND TeachedCourse.Semester=:Semester AND Course.Ident=:Ident AND TeachedCourse.Guarantor=:UserId LIMIT 1;';
} else {
    header('Location: /error/404');
    exit();
}

$courseDataQuery = $db->prepare($querySting);

$courseDataQuery->execute([
    ':Year' => $_GET['Year'].'/'.(substr($_GET['Year'], 2, 2) +1),
    ':Semester' => $_GET['Semester'],
    ':Ident' => $_GET['Ident'],
    ':UserId' => $_SESSION['UserId']
]);

//$courseDataQuery->bindParam(':UserId', $_SESSION['UserId'], PDO::PARAM_INT);
//$courseDataQuery->bindParam(':Year', $_GET['Year'], PDO::PARAM_INT);
//$courseDataQuery->bindParam(':Semester', $_GET['Semester'], PDO::PARAM_STR);
//$courseDataQuery->bindParam(':Ident', $_GET['Ident'], PDO::PARAM_STR);
//$courseDataQuery->execute();
//
//if ($courseDataQuery->rowCount()!=1) {
//    header('Location: /error/404');
//    exit();
//}

$courseData = $courseDataQuery->fetch(PDO::FETCH_ASSOC);

var_dump($courseData);

echo '<div class="breadcrumb_div">
            <div class="breadcrumbPath">
                <a href="/">Home</a>
                <p class="arrow">→</p>
            </div>
            <div class="breadcrumbPath">
                <p> ' . 'Course (' . htmlspecialchars($courseData['Ident']). ' in ' . htmlspecialchars($courseData['Semester']) . ' in ' . htmlspecialchars($courseData['Year']) . ')' . '</p>
            </div>
    </div>';

?>
<div class="checkbox_box">
    <div class="field">
        <label for="homework">Add homework</label>
        <input type="checkbox" id="homework" class="homework clickableBox" >
        <div id="homeworkSubMenu" style="display: block">
            <form method="post">
                <div class="field">
                    <label for="Name">Name: </label>
                    <input type="text" name="Name" id="Name" placeholder="ex. Hello World!" pattern="^\S+(\s)?\S*$" onkeypress="this.style.width = ((this.value.length + 1) * 8) + 'px';" required>
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
                    <label for="Visibility">Visibility: </label>
                    <input type="checkbox" name="Visibility" id="Visibility" value="true" >
                </div>
                <button type="submit" name="addHomework" value="true" >Add homework</button>
            </form>
        </div>
    </div>
</div>
<br>
<div>list of homeworks</div>

<?php
include __DIR__ . '/inc/footer.php';