<?php
require_once __DIR__.'/classes/Homework.php';
require_once __DIR__ . '/inc/db.php';

session_start();

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

if (empty($_SESSION['UserId'])){
    //uživatel už je přihlášený, nemá smysl, aby se přihlašoval znovu
    header('Location: login');
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

/**
 * File handling with creation of docker container
 */
if ($_FILES["myfile"] != null) {
    echo '<pre>';
    $fileName = $_FILES["myfile"]["tmp_name"];
//    var_dump($_FILES["myfile"]);
//    var_dump($fileName);
    $tempFile = tmpfile();
    fwrite($tempFile, $homework->getMarking());


    shell_exec("docker pull hosj03/docker-app:latest");

    $composeString = "docker compose -p " . $_SESSION['UserId'] . " up -d --quiet-pull --force-recreate 2>&1";
    $compose = shell_exec($composeString);
//    echo $compose;
//
//    docker container ls --filter name=localwebdev-app-1 | awk '/localwebdev-app-1/ {print $1}'
    $containerID = shell_exec("docker container ls --all --quiet --filter name=" . $_SESSION['UserId'] . "-app-1");

    $containerID = substr($containerID, 0, 12);

    $containerPort = shell_exec("docker port " . $containerID);

    $containerPort = substr($containerPort, strrpos($containerPort, ":") + 1);

    $containerPort = substr($containerPort, 0, strlen($containerPort) - 1);


//    docker cp  ~/Desktop/filename.txt container-id:/path/filename.txt

    $dockerCopyCommandRunFile = "docker cp " . $fileName . " " . $containerID . ":/var/www/html/test.java 2>&1";
    $dockerCopyCommandMarkingFile = "docker cp " . stream_get_meta_data($tempFile)["uri"] . " " . $containerID . ":/var/www/html/marking.json 2>&1";

    fclose($tempFile);
//    var_dump($dockerCopyCommand);

    $dockerCopy = shell_exec($dockerCopyCommandRunFile);
    $dockerCopy = shell_exec($dockerCopyCommandMarkingFile);
//    echo $dockerCopy;

    $systemUser = shell_exec('docker container ls 2>&1');
    echo $systemUser;

    if (strpos($systemUser, $_SESSION['UserId']) !== false) {
        echo "it worked";

        $curlCommand = "curl localhost:" . $containerPort;
//      var_dump($curlCommand);

        shell_exec($curlCommand);
    }
    echo '</pre>';



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

echo '<form id="uploadbanner" enctype="multipart/form-data" method="post" action="#">
        <input id="fileupload" name="myfile" type="file" />
        <input type="submit" value="submit" id="submit" />
    </form>';

include __DIR__ . '/inc/footer.php';