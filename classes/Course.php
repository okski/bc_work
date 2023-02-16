<?php
namespace classes;

require_once __DIR__ . '/Seminar.php';

class Course {
    private string $ident;
    private string $year;
    private string $semester;

    private Seminar $Seminar;

    /**
     * @param string $ident
     * @param string $year
     * @param string $semester
     * @param array $seminar
     */
    public function __construct(string $ident, string $year, string $semester, array $seminar)
    {
        $this->ident = $ident;
        $this->year = $year;
        $this->semester = $semester;
        $this->Seminar = new Seminar($seminar);

    }

    /**
     * @return string
     */
    public function getIdent(): string
    {
        return $this->ident;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getSemester(): string
    {
        return $this->semester;
    }

    /**
     * @return Seminar
     */
    public function getSeminar(): Seminar
    {
        return $this->Seminar;
    }



    public function __toString()
    {
        $seminarId = $this->Seminar->getSeminarId();
//        <div class="ident">' . $this->ident . '</div>
        $result = '<div class="course">';
        $result = $result . '<div class="year">' . $this->year . '</div>';
//        $result = $result . '<div class="semester">' . $this->semester . '</div>
//        <a href="../seminar.php?id=' . $seminarId . '">' . $this->ident . '</a>';
//        $result = $result . '</form></div>';
        $result = $result . '<div class="semester">' . $this->semester . '</div>
        <form action="/seminar.php">
        <input type="hidden" name="SeminarId" value="' . $seminarId . '">
        <input type="submit" value="' . $this->ident . '">';
        $result = $result . '</form></div>';


        return $result;
    }


}