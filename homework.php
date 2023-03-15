<?php
require_once __DIR__.'/classes/Homework.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: /login');
    exit();
}

include __DIR__ . '/inc/header.php';

$homework = null;
$submmitedHomeworks = null;

$homeworkDataQuery = $db->prepare('SELECT * FROM Homework WHERE HomeworkId=:HomeworkId LIMIT 1;');

$homeworkDataQuery->execute([
    ':HomeworkId' => $_GET["HomeworkId"]
]);
$homeworkData = $homeworkDataQuery->fetch(PDO::FETCH_ASSOC);

if (!empty($homeworkData)) {
    $homework = new \classes\Homework($homeworkData);
}

if (is_null($homework)) {
    header('Location: /error/404');
}

$submittedHomeworksDataQuery = $db->prepare('SELECT SubmittedHomework.* FROM SubmittedHomework WHERE HomeworkId=:HomeworkId AND StudentId=:StudentId ORDER BY DateTime DESC;');

$submittedHomeworksDataQuery->execute([
    ':HomeworkId' => $homework->getHomeworkId(),
    ':StudentId' => $_SESSION['UserId']
]);

$submittedHomeworksData = $submittedHomeworksDataQuery->fetchAll(PDO::FETCH_ASSOC);


/**
 * File handling with creation of docker container
 */
if ($_FILES["myfile"] != null) {
    echo '<pre>';
    $fileName = $_FILES["myfile"]["tmp_name"];
//    var_dump($_FILES["myfile"]);
//    var_dump($fileName);
    $tempMarkingFile = tmpfile();
    $tempDataFile = tmpfile();

    fwrite($tempMarkingFile, $homework->getMarking());
    fwrite($tempDataFile, $_SESSION['UserId'].":".$homework->getHomeworkId());


    shell_exec("docker pull hosj03/docker-app:latest -q && docker system prune -f");

    $composeString = "docker compose -p " . $_SESSION['UserId'] . "-" . $homework->getHomeworkId() . " up -d --quiet-pull --force-recreate 2>&1";
    $compose = shell_exec($composeString);
//    echo $compose;
//
//    docker container ls --filter name=localwebdev-app-1 | awk '/localwebdev-app-1/ {print $1}'
    $containerID = shell_exec("docker container ls --all --quiet --filter name=" . $_SESSION['UserId'] . "-" . $homework->getHomeworkId() . "-app-1");

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

    if (strpos($systemUser, $_SESSION['UserId']. "-" . $homework->getHomeworkId()  . "-app" ) !== false) {

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

if(isset($_SESSION["seminarBreadCrumb"])) {
    echo $_SESSION["seminarBreadCrumb"];
} else {
    echo 'Seminar (' . htmlspecialchars($_GET["SeminarId"]) . ')';
}

echo '</a>
            <p class="arrow">→</p>
        </div>
        <div class="breadcrumbPath">
            <p>' . $homework->getName() . '</p>
        </div>
    </div>';

$homework->printHomework();

echo '<form id="uploadbanner" enctype="multipart/form-data" method="post" action="#">
        <input id="fileupload" name="myfile" type="file" required />
        <input type="submit" value="submit" id="submit" />
    </form>';

echo '<div class="submittedHomeworks">';

printSubmittedHomeworks($submittedHomeworksData);

echo '</div>';
include __DIR__ . '/inc/footer.php';


function printSubmittedHomeworks($submittedHomeworksData) {
    foreach ($submittedHomeworksData as $submittedHomeworkData) {
        $tmpDownloadFile = tmpfile();
        fwrite($tmpDownloadFile, $submittedHomeworkData["SubmittedFile"]);

//        var_dump($submittedHomeworkData);

        echo '<div class="submittedHomework">';
        echo '<div class="submittedHomeworkTime">' . $submittedHomeworkData["DateTime"] . '</div>';
        echo '<div class="submittedHomeworkResult">' . $submittedHomeworkData["Result"] . '</div>';
        echo "<button type='submit' onclick='window.open(\"/download.php?fileId=" . $submittedHomeworkData["SubmittedHomeworkId"] . "\");'>Download</button><br>";
        echo '</div>';
//        fclose($tmpDownloadFile);
    }
}