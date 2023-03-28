<?php

namespace classes;

class SubmittedHomework
{
    private int $SubmittedHomeworkId;
    private int $StudentId;
    private int $Result;
    private string $SubmittedFile;
    private ?string $ResultFile;
    private string $DateTime;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->SubmittedHomeworkId = $data['SubmittedHomeworkId'];
        $this->StudentId = $data['StudentId'];
        $this->Result = $data['Result'];
        $this->SubmittedFile = $data['SubmittedFile'];
        if (!is_null($data['ResultFile'])) {
            $this->ResultFile = $data['ResultFile'];
        } else {
//            var_dump($this->ResultFile);
            $this->ResultFile = null;
        }
        $this->DateTime = $data['DateTime'];
    }

    /**
     * @return int
     */
    public function getSubmittedHomeworkId(): int
    {
        return $this->SubmittedHomeworkId;
    }

    /**
     * @return int
     */
    public function getStudentId(): int
    {
        return $this->StudentId;
    }

    /**
     * @return int
     */
    public function getResult(): int
    {
        return $this->Result;
    }

    /**
     * @return string
     */
    public function getSubmittedFile(): string
    {
        return $this->SubmittedFile;
    }

    /**
     * @return string
     */
    public function getResultFile(): string
    {
        return $this->ResultFile;
    }

    /**
     * @return string
     */
    public function getDateTime(): string
    {
        return $this->DateTime;
    }



}