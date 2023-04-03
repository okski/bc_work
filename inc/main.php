<?php
require_once __DIR__ . '/../classes/Course.php';
require_once __DIR__ . '/db.php';

$queryString = "";

if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.*, TeachedCourse.Guarantor FROM SeminarStudent INNER JOIN Seminar ON 
SeminarStudent.SeminarId=Seminar.SeminarId AND StudentId=:UserId INNER JOIN TeachedCourse ON 
TeachedCourse.TeachedCourseId=Seminar.TeachedCourseId INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId ORDER BY TeachedCourse.Year, TeachedCourse.Semester DESC;';
} elseif(isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
    $queryString = 'SELECT Course.Ident, TeachedCourse.Year, TeachedCourse.Semester, Seminar.*, TeachedCourse.Guarantor FROM TeachedCourse INNER JOIN Course ON
Course.CourseId=TeachedCourse.CourseId LEFT JOIN Seminar ON
Seminar.TeachedCourseId=TeachedCourse.TeachedCourseId WHERE Seminar.TeacherId=:UserId OR TeachedCourse.Guarantor=:UserId
ORDER BY TeachedCourse.Year, TeachedCourse.Semester DESC, FIELD(Day, \'Mon\', \'Tue\', \'Wed\', \'Thu\', \'Fri\', \'Sat\', \'Sun\'), TimeStart;';
}

$coursesDataQuery = $db->prepare($queryString);

$coursesDataQuery->execute([
    ':UserId' => $_SESSION['UserId']
]);

$coursesData = $coursesDataQuery->fetchAll(PDO::FETCH_ASSOC);
$courses = array();

echo '<div class="courses">';
if (!empty($coursesData)) {
    foreach ($coursesData as $courseData) {
        $seminarArr = array("SeminarId" => $courseData["SeminarId"], 'TeacherId' => $courseData['TeacherId'], 'Day' => $courseData['Day'], 'TimeStart' => $courseData['TimeStart'], 'TimeEnd' => $courseData['TimeEnd'], "homeworks" => null);
        $courses[$courseData['Year']][$courseData['Semester']][$courseData['Ident']][] = new classes\Course($courseData['Ident'], $courseData['Year'], $courseData['Semester'], $seminarArr, $courseData['Guarantor']);
    }
    foreach ($courses as $year => $yearCourses) {
        $link = substr($year, 0, 4) . '/';
        echo '<div class="coursesYear"><div class="year">' . $year . '</div>';
        foreach ($yearCourses as $semester => $semesterCourses) {
            $link = $link . $semester . '/';
            echo '<div class="semester">' . $semester . '</div>';
            foreach ($semesterCourses as $ident => $identCourses) {
                $link = $link . $ident;
                if (isset($_SESSION["Student"]) && $_SESSION["Student"] == 1) {
                    echo $identCourses[0]->toString('Student');
                }elseif (isset($_SESSION["Teacher"]) && $_SESSION["Teacher"] == 1) {
                    echo '<div class="course clickable" id="'.$ident.'"><div class="">' . $ident . '</div><div class="seminars" style="display: none;">';
                    if (!is_null($identCourses[0]->getGuarantorId()) && $identCourses[0]->getGuarantorId() == $_SESSION['UserId']) {
                        echo '<a href="/course/' . $link  . '">as Guarantor</a>';
                    }
                    foreach ($identCourses as $course) {
                        if ($course->getSeminar()->getSeminarId() != 0) {
                            echo $course->toString('Teacher');
                        }
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