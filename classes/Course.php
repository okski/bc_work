<?php
namespace classes;

require_once __DIR__ . '/Seminar.php';

class Course {
    private string $ident;
    private string $year;
    private string $semester;

    private int $seminarId;

    /**
     * @param string $ident
     * @param string $year
     * @param string $semester
     * @param int $seminar
     */
    public function __construct(string $ident, string $year, string $semester, int $seminar)
    {
        $this->ident = $ident;
        $this->year = $year;
        $this->semester = $semester;
        $this->seminarId = $seminar;

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
     * @return int
     */
    public function getSeminar(): int
    {
        return $this->seminarId;
    }

    public function __toString()
    {
        $link =  "seminar/" . $this->seminarId;

        $result = '<div class="course">';
        $result = $result . '<div class="year">' . $this->year . '</div>';
        $result = $result . '<div class="semester">' . $this->semester . '</div>
        <a href="/' . $link . '">' . $this->ident . '</a>';
        $result = $result . '</form></div>';


        return $result;
    }

}