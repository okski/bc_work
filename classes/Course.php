<?php
namespace classes;

require_once __DIR__ . '/Seminar.php';

class Course {
    private string $ident;
    private string $year;
    private string $semester;
    private int $seminarId;
    private int $GuarantorId;

    /**
     * @param string $ident
     * @param string $year
     * @param string $semester
     * @param string|null $seminar
     * @param string|null $GuarantorId
     */
    public function __construct(string $ident, string $year, string $semester, string $seminar = null, string $GuarantorId = null)
    {
        $this->ident = $ident;
        $this->year = $year;
        $this->semester = $semester;
        $this->seminarId = (int)$seminar;
        $this->GuarantorId = (int)$GuarantorId;
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

    /**
     * @return int
     */
    public function getSeminarId(): int
    {
        return $this->seminarId;
    }

    /**
     * @return int
     */
    public function getGuarantorId(): int
    {
        return $this->GuarantorId;
    }



    public function __toString()
    {
        $link =  "seminar/" . $this->seminarId;

        $result = '<div class="seminar">';

        $result = $result . '<a href="/' . $link . '">' . $this->ident . '</a>';

        return $result . '</div>';
    }

}