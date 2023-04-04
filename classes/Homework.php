<?php

namespace classes;

class Homework {

    private int $HomeworkId;

    private string $Name;

    private string $Description;

    private string $Marking;

    private int $Visible;

    private int $AddedBy;
    private bool $General;

    private string $InputFile;

    public function __construct(array $data) {
        $this->HomeworkId = $data['HomeworkId'];
        $this->Name = $data['Name'];
        $this->Description = $data['Description'];
        $this->Marking = $data['Marking'];
        $this->AddedBy = (int)$data['AddedBy'];
        $this->General = (bool)$data['General'];
        $this->Visible = (int)$data['Visible'];
        if (!empty($data['InputFile'])) {
            $this->InputFile = $data['InputFile'];
        } else {
            $this->InputFile = '';
        }
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
     * @return string
     */
    public function getMarking(): string
    {
        return $this->Marking;
    }

    /**
     * @return int
     */
    public function getVisible(): int
    {
        return $this->Visible;
    }

    /**
     * @return int
     */
    public function getAddedBy(): int
    {
        return $this->AddedBy;
    }

    /**
     * @return bool
     */
    public function isGeneral(): int
    {
        return $this->General;
    }

    /**
     * @return string
     */
    public function getInputFile(): string
    {
        return $this->InputFile;
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
        echo "<div class='homeworkInfo'>
                <div class='homeworkName'>" . $this->Name . "</div>
                <div class='homeworkDescription'>" . $this->Description . "</div></div>";
    }

}