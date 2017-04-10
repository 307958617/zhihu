<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function store(Request $request)
    {
        $hasDialog = Message::where('from_user_id',\Auth::guard('api')->user()->id)->where('to_user_id',request('user_id'))
                              ->orWhere('to_user_id',\Auth::guard('api')->user()->id)->where('from_user_id',request('user_id'))
                              ->first();//判断两个用户之间是否发送过私信，即是否产生过对话。
        $message = Message::create([
            'from_user_id'=> \Auth::guard('api')->user()->id,
            'to_user_id'  => request('user_id'),
            'body'        => request('body'),
            'dialog_id'   => $hasDialog ? $hasDialog->dialog_id : time().\Auth::guard('api')->user()->id//如果产生过对话就用原来的对话id，如果没有产生就生成新的对话id。
        ]);
        if($message){
            return response()->json(['status'=> true]);
        }
        return response()->json(['status'=> false]);
    }
}
