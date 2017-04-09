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
        $messages= Message::where('from_user_id',\Auth::id())->orWhere('to_user_id',\Auth::id())->with(['fromUser','toUser'])->get()->unique()->groupBy('to_user_id');

        return view('inbox.index',['messages' => $messages]);
    }

    public function show($id1,$id2)
    {
        $messages = Message::where('from_user_id','=',$id1)->where('to_user_id','=',$id2)
                    ->orWhere('from_user_id','=',$id2)->where('to_user_id',$id1)
                    ->latest()->get();
        return view('inbox.show',compact('messages','id1','id2'));
    }

    public function send($id1,$id2)
    {
        Message::create([
            'from_user_id' => $id1,
            'to_user_id'   => $id2,
            'body'         => request('body')
        ]);
        return back();
    }
}
