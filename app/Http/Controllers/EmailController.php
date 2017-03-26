<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function verify($token)
    {
        $user = User::where('confirmation_token',$token)->first();
        if (is_null($user)){
            //如果该用户不存在怎么样...
            return redirect('/');
        }
        $user->is_active = 1;  //激活用户
        $user->confirmation_token = str_random(40); //重置token
        $user->save();
        Auth::login($user);//登录
        return redirect('/home');//跳转

    }
}