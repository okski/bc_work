<?php
namespace classes;
require_once __DIR__ . '/Seminar.php';

class Course {
    private string $ident;
    private string $year;
    private string $semester;
    private Seminar $seminar;
    private int $GuarantorId;

    /**
     * @param string $ident
     * @param string $year
     * @param string $semester
     * @param array|null $seminar
     * @param string|null $GuarantorId
     */
    public function __construct(string $ident, string $year, string $semester, array $seminar = null, string $GuarantorId = null)
    {
        $this->ident = $ident;
        $this->year = $year;
        $this->semester = $semester;
        $this->seminar = new Seminar($seminar);
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
     * @return Seminar
     */
    public function getSeminar(): Seminar
    {
        return $this->seminar;
    }

    /**
     * @return int
     */
    public function getGuarantorId(): int
    {
        return $this->GuarantorId;
    }



    public function toString($role): string
    {
        $seminar = $this->getSeminar();
        $link =  "seminar/" . $seminar->getSeminarId();
        $result = '<div class="seminar">';

        if ($role == 'Student') {

            $result = $result . '<a href="/' . $link . '">' . $this->ident . '</a>';

            return $result . '</div>';
        }

        $result = $result . '<a href="/'. $link .'">Seminar (' . date_format(date_create($seminar->getTimeStart()),"H:i") . '-' . date_format(date_create($seminar->getTimeEnd()),"H:i") . ' on ' . date_format(date_create($seminar->getDay()), "l") . ')</a>';

        return $result . '</div>';

    }

}