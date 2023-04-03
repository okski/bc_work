<?php
namespace classes;

require_once __DIR__ . '/Homework.php';

class Seminar {

    private int $SeminarId;
    private int $TeacherId;
    private string $day;
    private string $timeStart;
    private string $timeEnd;
    private array $Homeworks = array();

    public function __construct(array $data) {
        if (!empty($data['SeminarId'])) {
            $this->SeminarId = $data['SeminarId'];
            $this->TeacherId = $data['TeacherId'];
            $this->day = $data['Day'];
            $this->timeStart = $data['TimeStart'];
            $this->timeEnd = $data['TimeEnd'];
            $this->initializeHomeworks($data["homeworks"]);

        } else {
            $this->SeminarId = 0;
            $this->TeacherId = 0;
            $this->day = '';
            $this->timeStart = '';
            $this->timeEnd = '';
            $this->Homeworks = array();
        }
    }

    /**
     * @return int
     */
    public function getSeminarId(): int
    {
        return $this->SeminarId;
    }

    /**
     * @return int
     */
    public function getTeacherId(): int
    {
        return $this->TeacherId;
    }

    /**
     * @return array
     */
    public function getHomeworks(): array
    {
        return $this->Homeworks;
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * @return string
     */
    public function getTimeStart(): string
    {
        return $this->timeStart;
    }

    /**
     * @return string
     */
    public function getTimeEnd(): string
    {
        return $this->timeEnd;
    }



    private function initializeHomeworks($homeworksData) {
        if (!empty($homeworksData)) {
            foreach ($homeworksData as $homeworkData) {
                $homework = new Homework($homeworkData);
                $this->Homeworks[] = $homework;
            }
        }
    }

    public function toString($role): string
    {
        $result = "<div class='homeworks'>";

        if ($role == 'Student') {
            if (!empty($this->Homeworks)) {
                $visibleCounter = 0;
                foreach ($this->Homeworks as $homework) {
                    if (!is_null($homework)) {
                        if ($homework->getVisible()) {
                            $result = $result . $homework->__toString();
                        } else {
                            $visibleCounter++;
                        }
                    }
                }
                if ($visibleCounter == count($this->Homeworks)) {
                    echo '<div>This seminar does not have any homeworks.</div>';
                }
            } else {
                echo '<div>This seminar does not have any homeworks.</div>';
            }
        } else {
            if (!empty($this->Homeworks)) {
                foreach ($this->Homeworks as $homework) {
                    if (!is_null($homework)) {
                        if (!$homework->isGeneral()) {
                            $result = $result . $homework->__toString();
                        }
                    }
                }
            } else {
                echo '<div>This seminar does not have any homeworks.</div>';
            }
        }

        $result = $result . "</div>";

        return $result;
    }
}