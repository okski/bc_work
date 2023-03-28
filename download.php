<?php
require_once __DIR__ . '/inc/db.php';

$fileId = '';
$queryString = '';


if (isset($_GET['fileId'])) {
    $fileId = $_GET['fileId'];
} else {
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'submitted') {
        $queryString = 'SELECT SubmittedHomework.SubmittedFile FROM SubmittedHomework WHERE SubmittedHomeworkId=:SubmittedHomeworkId LIMIT 1;';
        header("Content-Disposition: attachment; filename=file.java");
    } elseif ($_GET['type'] == 'result') {
        $queryString = 'SELECT SubmittedHomework.ResultFile FROM SubmittedHomework WHERE SubmittedHomeworkId=:SubmittedHomeworkId LIMIT 1;';
        header("Content-Disposition: attachment; filename=file.txt");

    } else {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
} else {
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

$fileQuery = $db->prepare($queryString);

$fileQuery->execute([
    ':SubmittedHomeworkId' => $fileId
]);

if ($fileQuery->rowCount()!=1) {
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

$file = $fileQuery->fetch(PDO::FETCH_ASSOC);


header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: binary");
if (!empty($file)) {
    if ($_GET['type'] == 'submitted') {
        echo $file['SubmittedFile'];
    } elseif ($_GET['type'] == 'result') {
        echo $file['ResultFile'];
    }
} else {
    header('Location: /');
}
