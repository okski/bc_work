<?php
namespace classes;

require_once __DIR__ . '/Homework.php';

class Seminar {

    private int $SeminarId;
    private array $Homeworks = array();

    public function __construct(array $data) {
        $this->SeminarId = $data['SeminarId'];
        $this->initializeHomeworks($data["homeworks"]);
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
        if (!empty(($this->Homeworks))) {
            foreach ($this->Homeworks as $homework) {
                if (!is_null($homework)) {
                    $result = $result . $homework->__toString();
                }
            }
        } else {
            echo '<div>This seminar does not have any homeworks.</div>';
        }

        return $result;
    }
}