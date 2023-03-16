<?php
require_once __DIR__.'/../classes/Course.php';
require_once 'db.php';

$querySting = "";

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    $querySting = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, TeachedCourse.Guarantor FROM SeminarStudent INNER JOIN Seminar ON 
SeminarStudent.SeminarId=Seminar.SeminarId AND StudentId=:UserId INNER JOIN TeachedCourse ON 
TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId ORDER BY TeachedCourse.Year, TeachedCourse.Semester DESC;';
} elseif(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $querySting = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.SeminarId, TeachedCourse.Guarantor FROM TeachedCourse INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId LEFT JOIN Seminar ON
Seminar.TeachedCourseId=TeachedCourse.TeachedCourseId WHERE Seminar.TeacherId=:UserId OR TeachedCourse.Guarantor=:UserId
ORDER BY TeachedCourse.Year, TeachedCourse.Semester DESC; ';
}

$coursesDataQuery = $db->prepare($querySting);

$coursesDataQuery->execute([
    ':UserId' => $_SESSION['UserId']
]);
$coursesData = $coursesDataQuery->fetchAll(PDO::FETCH_ASSOC);

$courses = array();

echo '<div class="courses">';
if (!empty($coursesData)) {
//    var_dump($coursesData);
    foreach ($coursesData as $courseData) {
//        var_dump($courseData);
        $courses[$courseData['Year']][$courseData['Semester']][$courseData['Ident']][] = new classes\Course($courseData['Ident'], $courseData['Year'], $courseData['Semester'], $courseData['SeminarId'], $courseData['Guarantor']);
//        $courses[] = $course;
        $course = new classes\Course($courseData['Ident'], $courseData['Year'], $courseData['Semester'], $courseData['SeminarId'], $courseData['Guarantor']);
//        print($course);
    }
//    var_dump($courses);

    if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {

    }elseif (isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {

    }
    foreach ($courses as $year => $yearCourses) {
        echo '<div class="coursesYear"><div class="year">' . $year . '</div>';
        foreach ($yearCourses as $semester => $semesterCourses) {
              echo '<div class="semester">' . $semester . '</div>';
              foreach ($semesterCourses as $ident => $identCourses) {

                  if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
                      print($identCourses[0]);
                  }elseif (isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
                      echo '<div class="course clickable" id="'.$ident.'"><div class="">' . $ident . '</div><div class="seminars" style="display: none;">';
                      foreach ($identCourses as $course) {
                          print($course);
                      }
                      echo '</div></div>';
                  }
              }

        }
        echo '</div>';
    }

    echo '</div>';
}
echo '</div>';