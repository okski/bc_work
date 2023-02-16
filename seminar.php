<?php
require_once __DIR__.'/classes/Course.php';

session_start();
//$seminarsDataQuery = $db->prepare('SELECT BcWork.SeminarStudent.StudentId, BcWork.Seminar.* from BcWork.SeminarStudent INNER JOIN BcWork.Seminar ON BcWork.SeminarStudent.SeminarId=BcWork.Seminar.SeminarId AND StudentId=:id;');
//
//$seminarsDataQuery->execute([
//    ':id'=>$_SESSION['UserId']
//]);
//
//$seminarsData = $seminarsDataQuery->fetchAll();
//
//var_dump($seminarsData);
//
//$seminars = array();

$seminar = null;


foreach ($_SESSION['courses'] as $course) {
    if (!is_null($course->getSeminar())) {
        if ($course->getSeminar()->getSeminarId() == $_GET['SeminarId']) {
            $seminar = $course->getSeminar();
        }
    }

}

if (!is_null($seminar) && !empty($seminar->getHomeworks())) {
    foreach ($seminar->getHomeworks() as $homework) {
        if (!is_null($homework)) {
            print($homework);
        }
    }
} else {
    echo '<h1>This seminar does not exist or does not have any homeworks.</h1>';
}
