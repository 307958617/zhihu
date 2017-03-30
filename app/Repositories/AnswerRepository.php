<?php

namespace App\Repositories;


use App\Answer;

class AnswerRepository
{
    public function createAnswer(array $answer)
    {
        return Answer::create($answer);
    }
}