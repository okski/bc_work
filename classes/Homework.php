<?php

namespace classes;

class Homework {

    private int $HomeworkId;

    private string $Name;

    public function __construct(array $data) {
        $this->HomeworkId = $data['HomeworkId'];
        $this->Name = $data['Name'];
    }

    public function __toString()
    {
        $result = 'homework id is: ' . $this->HomeworkId . '<br> name of homework is: ' . $this->Name;
        return $result;
    }


}