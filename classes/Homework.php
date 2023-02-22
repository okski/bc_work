<?php

namespace classes;

class Homework {

    private int $HomeworkId;

    private string $Name;

    private string $Description;

    private $Marking;

    public function __construct(array $data) {
        $this->HomeworkId = $data['HomeworkId'];
        $this->Name = $data['Name'];
        $this->Description = $data['Description'];
        $this->Marking = $data['Marking'];
    }

    /**
     * @return int
     */
    public function getHomeworkId(): int
    {
        return $this->HomeworkId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @return mixed
     */
    public function getMarking()
    {
        return $this->Marking;
    }



    public function __toString() {
        $link = "/seminar/" . htmlspecialchars($_GET['SeminarId']) . "/homework/" . $this->HomeworkId;

        $homeworkId = $this->HomeworkId;
        $result = '<div class="homework">';
        $result = $result . '<a href="' . $link . '">' . $this->Name .'</a></div>';

//        $result = 'homework id is: ' . $this->HomeworkId . '<br> name of homework is: ' . $this->Name;
        return $result;
    }

    public function printHomework() {
        echo "<div class='homework'>
                <div>" . $this->Name . "</div></div>";
    }

}