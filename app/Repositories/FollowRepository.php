<?php


namespace App\Repositories;


use App\Question;

class FollowRepository
{
    public function getNumbOfFollowers_byQuestionId($question)
    {
        $q = Question::withCount('followers')->find($question);
        return $q->followers_count;
    }
}