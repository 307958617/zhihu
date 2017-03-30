<?php

namespace App\Http\Controllers;

use App\Repositories\AnswerRepository;
use Illuminate\Http\Request;

class AnswersController extends Controller
{
    protected $answerRepository;

    public function __construct(AnswerRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function store(Request $request,$question)
    {
        $answer = $this->answerRepository->createAnswer([
            'user_id' => \Auth::id(),
            'question_id' => $question,
            'body' => $request->body
        ]);
        $answer->question()->increment('answers_count');
        return back();
    }
}
