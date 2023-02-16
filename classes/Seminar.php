<?php
namespace classes;

require_once __DIR__ . '/Homework.php';

class Seminar {

    private int $SeminarId;
    private array $Homeworks = array();

    public function __construct(array $data) {
        $this->SeminarId = $data['SeminarId'];
        $this->initializeHomeworks();
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



    private function initializeHomeworks() {
        require __DIR__ . '/../inc/db.php';

        $homeworksDataQuery = $db->prepare('SELECT Homework.* FROM SeminarHomework INNER JOIN Homework ON
        SeminarHomework.HomeworkId=Homework.HomeworkId AND SeminarHomework.SeminarId=:SeminarId;');

        $homeworksDataQuery->execute([
            ':SeminarId' => $this->SeminarId
        ]);

        $homeworksData = $homeworksDataQuery->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($homeworksData)) {
            foreach ($homeworksData as $homeworkData) {
                $homework = new Homework($homeworkData);
                $this->Homeworks[] = $homework;
            }
        }
    }

    public function __toString()
    {
        $result = $this->SeminarId;
        foreach ($this->Homeworks as $homework) {
            $result = $result . $homework;
        }
        ;
        return $result;
    }


}