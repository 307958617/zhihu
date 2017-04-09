<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function store(Request $request)
    {
        $message = Message::create([
            'from_user_id'=> \Auth::guard('api')->user()->id,
            'to_user_id'  => request('user_id'),
            'body'        => request('body')
        ]);
        if($message){
            return response()->json(['status'=> true]);
        }
        return response()->json(['status'=> false]);
    }
}
