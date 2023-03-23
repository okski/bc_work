<?php
namespace classes;

require_once __DIR__ . '/Homework.php';

class Seminar {

    private int $SeminarId;
    private string $day;
    private string $timeStart;
    private string $timeEnd;
    private array $Homeworks = array();

    public function __construct(array $data) {
        if (!empty($data['SeminarId'])) {
            $this->SeminarId = $data['SeminarId'];
            $this->day = $data['Day'];
            $this->timeStart = $data['TimeStart'];
            $this->timeEnd = $data['TimeEnd'];
            $this->initializeHomeworks($data["homeworks"]);
        } else {
            $this->SeminarId = 0;
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

    public function __toString() {
        $result = "<div class='homeworks'>";
        $result = $result . $this->toStringHomeworks();
        $result = $result . "</div>";

        return $result;
    }

    private function toStringHomeworks(): string
    {
        $result = "";
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

        return $result;
    }
}