<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class QuestionFollowController extends Controller
{

    protected $followRepository;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function follow($question)
    {
        Auth::user()->followThis($question);

        return back();
    }

}
