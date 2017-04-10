<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;

class InboxController extends Controller
{

    /**
     * InboxController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $messages= Message::where('from_user_id',\Auth::id())->orWhere('to_user_id',\Auth::id())->with(['fromUser','toUser'])->get()->groupBy('dialog_id');

        return view('inbox.index',['messages' => $messages]);
    }

    public function show($dialog_id)
    {
        $messages = Message::where('dialog_id',$dialog_id)->latest()->get();
        return view('inbox.show',compact('messages','dialog_id'));
    }

    public function store($dialog_id)
    {
        $message = Message::where('dialog_id',$dialog_id)->first();
        $toUserId = ($message->to_user_id == \Auth::id()) ? $message->from_user_id : $message->to_user_id;
        Message::create([
            'from_user_id' => \Auth::id(),
            'to_user_id'   => $toUserId,
            'body'         => request('body'),
            'dialog_id'    => $dialog_id
        ]);
        return back();
    }
}
