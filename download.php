<?php
require_once __DIR__ . '/inc/db.php';

$fileId = $_GET['fileId'];

$fileQuery = $db->prepare('SELECT SubmittedHomework.SubmittedFile FROM SubmittedHomework WHERE SubmittedHomeworkId=:SubmittedHomeworkId LIMIT 1;');

$fileQuery->execute([
    ':SubmittedHomeworkId' => $fileId
]);

if ($fileQuery->rowCount()!=1) {
    header('Location: ' . $_SERVER['REQUEST_URI']);
}

$file = $fileQuery->fetch(PDO::FETCH_ASSOC);


header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=file.java");
header("Content-Type: application/zip");
header("Content-Transfer-Encoding: binary");
if (!empty($file)) {
    echo $file['SubmittedFile'];
} else {
    header('Location: /');
}
