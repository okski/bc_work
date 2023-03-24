<?php
require_once __DIR__.'/classes/Course.php';
require_once __DIR__.'/classes/SubmittedHomework.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$course = null;
$submittedHomeworks = null;
$homeworkQueryString = '';
$submittedHomeworkQueryString = '';
$submittedHomeworkQueryArr = array();

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    $homeworkQueryString = 'SELECT *  FROM SeminarHomework INNER JOIN Seminar ON SeminarHomework.SeminarId = Seminar.SeminarId AND SeminarHomework.SeminarId=:SeminarId
                        INNER JOIN Homework ON SeminarHomework.HomeworkId = Homework.HomeworkId AND SeminarHomework.HomeworkId=:HomeworkId
                        INNER JOIN TeachedCourse ON Seminar.TeachedCourseId = TeachedCourse.TeachedCourseId
                        INNER JOIN Course ON TeachedCourse.CourseId = Course.CourseId;';

    $submittedHomeworkQueryString = 'SELECT SubmittedHomework.* FROM Homework INNER JOIN SeminarHomework ON Homework.HomeworkId = SeminarHomework.HomeworkId AND SeminarId=:SeminarId
                                        INNER JOIN SubmittedHomework ON Homework.HomeworkId = SubmittedHomework.HomeworkId AND Homework.HomeworkId=:HomeworkId
                                        WHERE StudentId=:StudentId ORDER BY DateTime DESC;';

    $submittedHomeworkQueryArr = [
        ':HomeworkId' => $_GET["HomeworkId"],
        ':SeminarId' => $_GET["SeminarId"],
        ':StudentId' => $_SESSION['UserId']
    ];
} elseif(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $homeworkQueryString = 'SELECT *  FROM SeminarHomework INNER JOIN Seminar ON SeminarHomework.SeminarId = Seminar.SeminarId AND SeminarHomework.SeminarId=:SeminarId
                        INNER JOIN Homework ON SeminarHomework.HomeworkId = Homework.HomeworkId AND SeminarHomework.HomeworkId=:HomeworkId
                        INNER JOIN TeachedCourse ON Seminar.TeachedCourseId = TeachedCourse.TeachedCourseId
                        INNER JOIN Course ON TeachedCourse.CourseId = Course.CourseId;';

    $submittedHomeworkQueryString = 'SELECT SubmittedHomework.*, User.Username FROM Homework INNER JOIN SeminarHomework ON Homework.HomeworkId = SeminarHomework.HomeworkId AND SeminarId=:SeminarId
                                        INNER JOIN SubmittedHomework ON Homework.HomeworkId = SubmittedHomework.HomeworkId AND Homework.HomeworkId=:HomeworkId
                                        INNER JOIN Student ON SubmittedHomework.StudentId = Student.StudentId
                                        INNER JOIN User ON Student.StudentId = User.UserId ORDER BY User.Username, DateTime DESC;';

    $submittedHomeworkQueryArr = [
        ':HomeworkId' => $_GET["HomeworkId"],
        ':SeminarId' => $_GET["SeminarId"]
    ];
} else {
    header('Location: /error/404');
    exit();
}

$homeworkDataQuery = $db->prepare($homeworkQueryString);

$homeworkDataQuery->execute([
    ':SeminarId' => $_GET['SeminarId'],
    ':HomeworkId' => $_GET["HomeworkId"]
]);

if ($homeworkDataQuery->rowCount()!=1) {
    header('Location: /error/404');
    exit();
}

$homeworkData = $homeworkDataQuery->fetch(PDO::FETCH_ASSOC);


if ($homeworkData['Visible'] != 1) {
    header('Location: /error/404');
}

if (!empty($homeworkData)) {
    $seminar = array("SeminarId" => $homeworkData["SeminarId"], 'Day' => $homeworkData['Day'], 'TimeStart' => $homeworkData['TimeStart'], 'TimeEnd' => $homeworkData['TimeEnd'], "homeworks" => array($homeworkData));
    $course = new \classes\Course($homeworkData['Ident'], $homeworkData['Year'], $homeworkData['Semester'], $seminar, $homeworkData['GuarantorId']);
}
if (is_null($course->getSeminar()->getHomeworks()[0])) {
    header('Location: /error/404');
}

$submittedHomeworksDataQuery = $db->prepare($submittedHomeworkQueryString);

$submittedHomeworksDataQuery->execute($submittedHomeworkQueryArr);

$submittedHomeworksData = $submittedHomeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);


/**
 * File handling with creation of docker container
 */
if ($_FILES["myfile"] != null) {
    echo '<pre>';
    $fileName = $_FILES["myfile"]["tmp_name"];
    $tempMarkingFile = tmpfile();
    $tempDataFile = tmpfile();

    fwrite($tempMarkingFile, $course->getSeminar()->getHomeworks()[0]->getMarking());
    fwrite($tempDataFile, $_SESSION['UserId'].":".$course->getSeminar()->getHomeworks()[0]->getHomeworkId());


    shell_exec("docker pull hosj03/docker-app:latest -q && docker system prune -f");

    $composeString = "docker compose -p " . $_SESSION['UserId'] . "-" . $course->getSeminar()->getHomeworks()[0]->getHomeworkId() . " up -d --quiet-pull --force-recreate 2>&1";
    $compose = shell_exec($composeString);
//    echo $compose;
//
//    docker container ls --filter name=localwebdev-app-1 | awk '/localwebdev-app-1/ {print $1}'
    $containerID = shell_exec("docker container ls --all --quiet --filter name=" . $_SESSION['UserId'] . "-" . $course->getSeminar()->getHomeworks()[0]->getHomeworkId() . "-app-1");

    $containerID = substr($containerID, 0, 12);

    $containerPort = shell_exec("docker port " . $containerID);

    $containerPort = substr($containerPort, strrpos($containerPort, ":") + 1);

    $containerPort = substr($containerPort, 0, strlen($containerPort) - 1);


//    docker cp  ~/Desktop/filename.txt container-id:/path/filename.txt

    $dockerCopyCommandRunFile = "docker cp " . $fileName . " " . $containerID . ":/home/user/test.java 2>&1";
    $dockerCopyCommandMarkingFile = "docker cp " . stream_get_meta_data($tempMarkingFile)["uri"] . " " . $containerID . ":/home/user/marking.json 2>&1";
    $dockerCopyCommandDataFile = "docker cp " . stream_get_meta_data($tempDataFile)["uri"] . " " . $containerID . ":/home/user/data 2>&1";
    $changeOwnershipCommand = "\"chown -R user:www-data /home/user && chmod -R u-rw,g+rw /home/user && chmod u+rwx /home/user/test.java\"";


    shell_exec($dockerCopyCommandRunFile);
    shell_exec($dockerCopyCommandMarkingFile);
    shell_exec($dockerCopyCommandDataFile);
    shell_exec("docker exec " . $containerID . " sh -c " . $changeOwnershipCommand);

    fclose($tempMarkingFile);
    fclose($tempDataFile);

    $systemUser = shell_exec('docker container ls 2>&1');

    if (strpos($systemUser, $_SESSION['UserId']. "-" . $course->getSeminar()->getHomeworks()[0]->getHomeworkId()  . "-app" ) !== false) {

        $curlCommand = "curl --max-time 1 localhost:" . $containerPort;
        $stopCommand = "docker stop " . $containerID . " > /dev/null &";


        shell_exec($curlCommand);
//        shell_exec($stopCommand);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
}


echo '<div class="breadcrumb_div">
        <div class="breadcrumbPath">
            <a href="/">Home</a>
            <p class="arrow">→</p>
        </div>
        <div class="breadcrumbPath">
            <a href="/seminar/' . htmlspecialchars($_GET["SeminarId"]) . '">';

$seminarInfo = htmlspecialchars($course->getIdent()) . ' in ' . htmlspecialchars($course->getSemester()) . ' in ' . htmlspecialchars($course->getYear()) . ' at ' . date_format(date_create($course->getSeminar()->getTimeStart()),"H:i") . '-' . date_format(date_create($course->getSeminar()->getTimeEnd()),"H:i") . ' on ' . date_format(date_create($course->getSeminar()->getDay()), "l");


echo 'Seminar (' . $seminarInfo . ')';

echo '</a>
            <p class="arrow">→</p>
        </div>
        <div class="breadcrumbPath">
            <p>' . $course->getSeminar()->getHomeworks()[0]->getName() . '</p>
        </div>
    </div>';

$course->getSeminar()->getHomeworks()[0]->printHomework();

if ($_SESSION['UserId'] == $course->getSeminar()->getHomeworks()[0]->getAddedBy() && !$course->getSeminar()->getHomeworks()[0]->isGeneral()) {
    echo '<form action="'.$_SESSION['rdrurl'].'/edit" method="post">
<input type="hidden" name="HomeworkId" id="HomeworkId" value="' . $course->getSeminar()->getHomeworks()[0]->getHomeworkId() . '">
<input type="hidden" name="Seminar" id="Seminar" value="' . htmlspecialchars($seminarInfo) . '">
<input type="hidden" name="General" id="General" value="0">
<button type="submit">Edit</button>
</form>';
}

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    echo '<form id="uploadbanner" enctype="multipart/form-data" method="post" action="#">
        <input id="fileupload" name="myfile" type="file" required />
        <input type="submit" value="submit" id="submit" />
    </form>';

    echo '<div class="submittedHomeworks">';
}


foreach ($submittedHomeworksData as $submittedHomeworkData) {
    $submittedHomeworks[$submittedHomeworkData['Username']][] = new \classes\SubmittedHomework($submittedHomeworkData);

}

//var_dump($submittedHomeworks);

printSubmittedHomeworks($submittedHomeworks);

echo '</div>';
include __DIR__ . '/inc/footer.php';



function printSubmittedHomeworks($submittedHomeworks) {
    foreach ($submittedHomeworks as $username => $usernameSubmittedHomeworks) {
        echo '<div class="homeworksUsername"><div class="username clickableSibling">' . $username . '</div>';
        foreach ($usernameSubmittedHomeworks as $submittedHomework) {
            echo '<div class="homework" style="display: none">';
            $tmpDownloadFile = tmpfile();
            fwrite($tmpDownloadFile, $submittedHomework->getSubmittedFile());

            echo '<div class="submittedHomework">';
            echo '<div class="submittedHomeworkTime">' . $submittedHomework->getDateTime() . '</div>';
            echo '<div class="submittedHomeworkResult">' . $submittedHomework->getResult() . '</div>';
            echo "<button type='submit' onclick='window.open(\"/download.php?fileId=" . $submittedHomework->getSubmittedHomeworkId() . "\");'>Download</button><br>";
            echo '</div></div>';
        }
        echo '</div>';
//        fclose($tmpDownloadFile);
    }
}